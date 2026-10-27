<?php
require __DIR__.'/../includes/modules.php';$user=require_role('admin');$error='';
if(post()){check_csrf();try{if(($_POST['action']??'')!=='review')throw new InvalidArgumentException('Invalid action.');$id=record_id($_POST['id']??'');$status=choice('status',['Pending','Approved','Rejected']);$note=text_value('admin_note',1000,false);if(!query_rows('SELECT id FROM leave_requests WHERE id=?',[$id]))missing();execute_sql('UPDATE leave_requests SET status=?,admin_note=? WHERE id=?',[$status,$note,$id]);flash('Leave decision saved.');redirect('admin/leave.php');}catch(InvalidArgumentException $e){$error=$e->getMessage();}}
page_start('Leave Requests',$user);error_notice($error);require __DIR__.'/../includes/leave-list.php';page_end();
