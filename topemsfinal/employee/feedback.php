<?php
require __DIR__.'/../includes/bootstrap.php'; require __DIR__.'/../includes/layout.php';
$user=require_role('employee');$error='';$edit=null;
if (isset($_GET['edit'])) { $q=db()->prepare('SELECT * FROM feedback WHERE id=? AND employee_id=?');$q->execute([record_id($_GET['edit']),$user['id']]);$edit=$q->fetch();if (!$edit) missing(); }
if (post()) {
 check_csrf();
 try {
  $action=$_POST['action']??'';
  if ($action==='delete') { $q=db()->prepare('DELETE FROM feedback WHERE id=? AND employee_id=?');$q->execute([record_id($_POST['id']??''),$user['id']]);if (!$q->rowCount()) missing();flash('Feedback deleted.');redirect('employee/feedback.php'); }
  if ($action!=='save') throw new InvalidArgumentException('Invalid action.');
  $category=text_value('category',30);$message=text_value('message',2000);
  if (!in_array($category,['General','Workplace','Salary','Suggestion'],true)) throw new InvalidArgumentException('Choose a valid category.');
  $id=empty($_POST['id'])?null:record_id($_POST['id']);
  if ($id) {
   $q=db()->prepare('SELECT id FROM feedback WHERE id=? AND employee_id=?');$q->execute([$id,$user['id']]);if (!$q->fetch()) missing();
   $q=db()->prepare("UPDATE feedback SET category=?,message=?,status='Pending',reply=NULL,updated_at=CURRENT_TIMESTAMP WHERE id=? AND employee_id=?");$q->execute([$category,$message,$id,$user['id']]);
  } else { $q=db()->prepare('INSERT INTO feedback (employee_id,category,message) VALUES (?,?,?)');$q->execute([$user['id'],$category,$message]); }
  flash($id?'Feedback updated and returned to Pending for review.':'Feedback submitted.');redirect('employee/feedback.php');
 } catch (InvalidArgumentException $e) { $error=$e->getMessage();$edit=$_POST; }
}
$q=db()->prepare('SELECT * FROM feedback WHERE employee_id=? ORDER BY id DESC');$q->execute([$user['id']]);$rows=$q->fetchAll();page_start('My Feedback',$user);
if ($error) echo '<p class="notice error-notice" role="alert">'.h($error).'</p>';
?><section class="card record-form"><h2 class="card-title"><?=empty($edit['id'])?'Share Your Feedback':'Edit Feedback'?></h2><form method="post"><?=csrf_field()?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?=h($edit['id']??'')?>"><div class="form-group"><label for="category">Category</label><select name="category" id="category" class="form-control"><?php foreach (['General','Workplace','Salary','Suggestion'] as $c): ?><option <?=$c===($edit['category']??'General')?'selected':''?>><?=h($c)?></option><?php endforeach; ?></select></div><div class="form-group"><label for="message">Message</label><textarea id="message" name="message" class="form-control" rows="5" required maxlength="2000"><?=h($edit['message']??'')?></textarea></div><div class="actions"><button class="btn btn-primary">Save Feedback</button><a class="btn btn-secondary" href="feedback.php">Clear / Cancel</a></div></form></section><section class="card"><h2 class="card-title">My Feedback History</h2><?php if (!$rows): ?><p>No feedback submitted yet.</p><?php endif; foreach ($rows as $f): ?><article class="feedback-item"><h3><?=h($f['category'])?> <span class="badge <?=$f['status']==='Pending'?'badge-warning':'badge-success'?>"><?=h($f['status'])?></span></h3><p class="feedback-meta">Submitted <?=h($f['created_at'])?></p><p><?=h($f['message'])?></p><?php if ($f['reply']): ?><strong>Admin response</strong><p><?=h($f['reply'])?></p><?php endif; ?><div class="actions"><a class="btn btn-primary" href="?edit=<?=(int)$f['id']?>">Edit</a><form method="post" data-confirm="Delete your feedback?"><?=csrf_field()?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=(int)$f['id']?>"><button class="btn btn-danger">Delete</button></form></div></article><?php endforeach; ?></section><?php page_end();
