<?php
require __DIR__.'/../includes/bootstrap.php'; require __DIR__.'/../includes/layout.php';
$user = require_role('admin'); $error = ''; $edit = null;
if (isset($_GET['edit'])) { $q=db()->prepare("SELECT * FROM users WHERE id=? AND role='employee'"); $q->execute([record_id($_GET['edit'])]); $edit=$q->fetch(); if (!$edit) missing(); }
if (post()) {
    check_csrf();
    try {
        $action=$_POST['action'] ?? '';
        if ($action === 'delete') {
            $q=db()->prepare("DELETE FROM users WHERE id=? AND role='employee'"); $q->execute([record_id($_POST['id'] ?? '')]); if (!$q->rowCount()) missing();
            flash('Employee and their linked feedback, salary, attendance and leave records deleted.'); redirect('admin/employees.php');
        }
        if ($action !== 'save') throw new InvalidArgumentException('Invalid action.');
        $id=empty($_POST['id']) ? null : record_id($_POST['id']);
        $code=text_value('employee_code',30); $name=text_value('full_name',120); $email=text_value('email',180); $phone=text_value('phone',30,false); $dept=text_value('department',100); $designation=text_value('designation',100); $username=text_value('username',80); $joined=text_value('join_date',10); $address=text_value('address',1000,false);
        $departmentQuery = db()->prepare('SELECT name FROM departments WHERE name = ?');
        $departmentQuery->execute([$dept]);
        $savedDepartment = $departmentQuery->fetchColumn();
        if ($savedDepartment === false) throw new InvalidArgumentException('Choose an existing department. Add one under Departments first.');
        $dept = $savedDepartment;
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('Enter a valid email address.');
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,80}$/D',$username)) throw new InvalidArgumentException('Username needs 3–80 letters, digits, dots, underscores or hyphens.');
        $date=DateTime::createFromFormat('!Y-m-d',$joined); if (!$date || $date->format('Y-m-d')!==$joined) throw new InvalidArgumentException('Enter a valid joining date.');
        $password=is_string($_POST['password']??null)?$_POST['password']:'';
        if ((!$id || $password!=='') && (strlen($password)<8 || strlen($password)>72)) throw new InvalidArgumentException('Password must contain 8–72 bytes.');
        if ($id) {
            $q=db()->prepare("SELECT id FROM users WHERE id=? AND role='employee'");$q->execute([$id]);if (!$q->fetch()) missing();
            $values=[$code,$name,$email,$phone,$dept,$designation,$username,$joined,$address];
            $sql='UPDATE users SET employee_code=?,full_name=?,email=?,phone=?,department=?,designation=?,username=?,join_date=?,address=?';
            if ($password!=='') { $sql.=',password_hash=?,session_version=session_version+1';$values[]=password_hash($password,PASSWORD_DEFAULT); }
            $values[]=$id;$q=db()->prepare($sql." WHERE id=? AND role='employee'");$q->execute($values);
        } else {
            $q=db()->prepare("INSERT INTO users (role,employee_code,full_name,email,phone,department,designation,username,join_date,address,password_hash) VALUES ('employee',?,?,?,?,?,?,?,?,?,?)");
            $q->execute([$code,$name,$email,$phone,$dept,$designation,$username,$joined,$address,password_hash($password,PASSWORD_DEFAULT)]);
        }
        flash($id?'Employee updated.':'Employee created. They can log in with the username and password you assigned.'); redirect('admin/employees.php');
    } catch (InvalidArgumentException $e) { $error=$e->getMessage(); }
      catch (PDOException $e) { if ($e->getCode()!=='23000') throw $e; $error='Employee ID, email or username is already in use.'; }
    $edit=$_POST;
}
$search=trim(is_string($_GET['q']??null)?$_GET['q']:'');$q=db()->prepare("SELECT * FROM users WHERE role='employee' AND (full_name LIKE ? OR employee_code LIKE ? OR department LIKE ? OR email LIKE ?) ORDER BY id DESC");$q->execute(array_fill(0,4,'%'.$search.'%'));$rows=$q->fetchAll();
$departments = db()->query('SELECT name FROM departments ORDER BY name')->fetchAll(PDO::FETCH_COLUMN);
page_start('Employee Management',$user);
if ($error) echo '<p class="notice error-notice" role="alert">'.h($error).'</p>';
?><section class="card record-form"><h2 class="card-title"><?=empty($edit['id'])?'Add Employee':'Edit Employee'?></h2><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=h($edit['id']??'')?>"><div class="form-grid"><?php
foreach (['employee_code'=>'Employee ID','full_name'=>'Full Name','email'=>'Email','phone'=>'Phone','department'=>'Department','designation'=>'Designation','username'=>'Login Username','join_date'=>'Joining Date','address'=>'Address'] as $key=>$label) {
    if ($key === 'department') { ?>
        <div class="form-group"><label for="department">Department</label><select class="form-control" id="department" name="department" required>
        <option value="">Select department</option>
        <?php foreach ($departments as $department): ?><option value="<?=h($department)?>" <?=($edit['department']??'')===$department?'selected':''?>><?=h($department)?></option><?php endforeach; ?>
        </select><?php if (!$departments): ?><p>No departments yet. <a href="departments.php">Add a department</a> before creating an employee.</p><?php endif; ?></div>
    <?php } else {
        input_field($key,$label,$edit[$key]??'', $key==='email'?'email':($key==='join_date'?'date':'text'),!in_array($key,['phone','address'],true));
    }
}
input_field('password',empty($edit['id'])?'Login Password':'New Password (leave blank to keep current)','', 'password',empty($edit['id']),'minlength="8" maxlength="72" autocomplete="new-password"');
?></div><div class="actions"><button class="btn btn-primary" type="submit">Save Employee</button><a class="btn btn-secondary" href="employees.php">Clear / Cancel</a></div></form></section><section class="card"><form method="get"><label for="search">Search employees</label><div class="actions"><input class="form-control" id="search" name="q" value="<?=h($search)?>" placeholder="Name, ID, email or department"><button class="btn btn-primary">Search</button></div></form><div class="table-container"><table class="table"><thead><tr><th>ID</th><th>Name / Username</th><th>Email / Phone</th><th>Department / Designation</th><th>Joining Date / Address</th><th>Actions</th></tr></thead><tbody><?php foreach ($rows as $e): ?><tr><td><?=h($e['employee_code'])?></td><td><?=h($e['full_name'])?><br><?=h($e['username'])?></td><td><?=h($e['email'])?><br><?=h($e['phone'])?></td><td><?=h($e['department'])?><br><?=h($e['designation'])?></td><td><?=h($e['join_date'])?><br><?=h($e['address'])?></td><td><div class="actions"><a class="btn btn-primary" href="?edit=<?=(int)$e['id']?>">Edit</a><form method="post" data-confirm="Delete this employee and ALL their feedback, salary, attendance and leave records?"><?=csrf_field()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$e['id']?>"><button class="btn btn-danger">Delete</button></form></div></td></tr><?php endforeach; if (!$rows): ?><tr><td colspan="6">No employees found.</td></tr><?php endif; ?></tbody></table></div></section><?php page_end();
