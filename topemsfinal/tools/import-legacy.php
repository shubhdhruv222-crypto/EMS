<?php
// Optional, one-time CLI import. Run only after importing the new schema.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../includes/bootstrap.php';
$options=getopt('', ['source:', 'reset-password:', 'salary-month:']);
$source=$options['source']??'ems';$reset=$options['reset-password']??'';$month=$options['salary-month']??'';
if (!preg_match('/^[A-Za-z0-9_]+$/D',$source) || strlen($reset)<8 || strlen($reset)>72 || !preg_match('/^(?:19|20|21)\d{2}-(?:0[1-9]|1[0-2])$/D',$month)) {
 fwrite(STDERR,"Usage: php tools/import-legacy.php --source=ems --reset-password=ChooseNewPassword --salary-month=2025-12\nThis resets imported employee passwords and assigns the specified month to legacy salaries. Run ONCE on a fresh target database.\n");exit(1);
}
$db=db();$map=[];$numericMap=[];$names=[];$counts=['employees'=>0,'feedback'=>0,'salaries'=>0,'skipped_employees'=>0,'skipped_feedback'=>0,'skipped_salaries'=>0];
$db->beginTransaction();
try {
 foreach ($db->query("SELECT * FROM `$source`.`employee`")->fetchAll() as $old) {
  $code=trim((string)$old['Employee ID']);$name=trim($old['Full Name']);$email=trim($old['Email']);$username=trim($old['Username']);$joined=$old['Join Date'];$date=DateTime::createFromFormat('!Y-m-d',$joined);
  if (!$code || strlen($code)>30 || !$name || !filter_var($email,FILTER_VALIDATE_EMAIL) || !preg_match('/^[A-Za-z0-9_.-]{3,80}$/D',$username) || !$date || $date->format('Y-m-d')!==$joined) { $counts['skipped_employees']++;continue; }
  $q=$db->prepare('SELECT id FROM users WHERE username=? OR employee_code=? OR email=?');$q->execute([$username,$code,$email]);if ($q->fetch()) { $counts['skipped_employees']++;continue; }
  $q=$db->prepare("INSERT INTO users (role,employee_code,full_name,email,phone,department,designation,join_date,address,username,password_hash) VALUES ('employee',?,?,?,?,?,?,?,?,?,?)");
  $q->execute([$code,$name,$email,(string)$old['Phone'],$old['Department'],$old['Role / Designation'],$joined,$old['Address'],$username,password_hash($reset,PASSWORD_DEFAULT)]);
  $id=(int)$db->lastInsertId();$map[$code]=$id;$names[$name][]=$id;
  if (preg_match('/^\d+$/D',$code)) $numericMap[(int)$code][]=$id;
  $counts['employees']++;
 }
 foreach ($db->query("SELECT * FROM `$source`.`feedback1`")->fetchAll() as $old) {
  $candidate=$numericMap[(int)$old['emp_id']]??[];
  if (count($candidate)!==1 || strlen(trim($old['message']))===0 || strlen($old['message'])>2000) { $counts['skipped_feedback']++;continue; }
  $q=$db->prepare('INSERT INTO feedback (employee_id,message,created_at) VALUES (?,?,?)');$q->execute([$candidate[0],$old['message'],$old['created_at']]);$counts['feedback']++;
 }
 foreach ($db->query("SELECT * FROM `$source`.`salary`")->fetchAll() as $old) {
  $candidate=$names[trim($old['empName'])]??[];$basic=(float)$old['baseSalary'];$allowances=(float)$old['allowances'];$deductions=(float)$old['deductions'];$net=round($basic+$allowances-$deductions,2);
  if (count($candidate)!==1 || min($basic,$allowances,$deductions,$net)<0 || $net>99999999.99) { $counts['skipped_salaries']++;continue; }
  $q=$db->prepare('SELECT id FROM salaries WHERE employee_id=? AND salary_month=?');$q->execute([$candidate[0],$month.'-01']);if ($q->fetch()) { $counts['skipped_salaries']++;continue; }
  $q=$db->prepare('INSERT INTO salaries (employee_id,salary_month,basic,allowances,deductions,net_salary) VALUES (?,?,?,?,?,?)');$q->execute([$candidate[0],$month.'-01',$basic,$allowances,$deductions,$net]);$counts['salaries']++;
 }
 $db->commit();echo json_encode($counts,JSON_PRETTY_PRINT)."\nImport completed. Imported salaries are Pending because the old table has no payment status. Review skipped or damaged old records manually.\n";
} catch (Throwable $e) { $db->rollBack();fwrite(STDERR,"Import rolled back. Check source database/table names and database access.\n");error_log((string)$e);exit(1); }
