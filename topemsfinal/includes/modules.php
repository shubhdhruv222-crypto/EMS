<?php
require_once __DIR__.'/bootstrap.php';
require_once __DIR__.'/layout.php';
function query_rows(string $sql, array $args=[]): array { $q=db()->prepare($sql); $q->execute($args); return $q->fetchAll(); }
function execute_sql(string $sql,array $args=[]): void { $q=db()->prepare($sql);$q->execute($args); }
function date_value(string $key): string { $v=text_value($key,10);$d=DateTime::createFromFormat('!Y-m-d',$v);if(!$d || $d->format('Y-m-d')!==$v) throw new InvalidArgumentException('Enter a valid date.');return $v; }
function choice(string $key,array $options): string { $v=text_value($key,30);if(!in_array($v,$options,true)) throw new InvalidArgumentException('Choose a valid '.$key.'.');return $v; }
function employee_value(): int { $id=record_id($_POST['employee_id']??'');if(!query_rows("SELECT id FROM users WHERE id=? AND role='employee'",[$id])) throw new InvalidArgumentException('Choose an existing employee.');return $id; }
function select_field(string $key,string $label,array $options,$value=''): void { echo '<div class="form-group"><label>'.h($label).'</label><select class="form-control" name="'.h($key).'" required>';foreach($options as $k=>$v) echo '<option value="'.h($k).'" '.((string)$k===(string)$value?'selected':'').'>'.h($v).'</option>';echo '</select></div>'; }
function options(array $values): array { return array_combine($values,$values); }
function employee_options(): array { $out=[];foreach(query_rows("SELECT id,full_name,employee_code FROM users WHERE role='employee' ORDER BY full_name") as $r)$out[$r['id']]=$r['employee_code'].' — '.$r['full_name'];return $out; }
function error_notice(string $error): void { if($error)echo '<p class="notice error-notice" role="alert">'.h($error).'</p>'; }
function delete_form(int $id): void { ?><form method="post" data-confirm="Delete this record?"><?=csrf_field()?><input type="hidden" name="id" value="<?=$id?>"><button class="btn btn-danger" name="action" value="delete">Delete</button></form><?php }
