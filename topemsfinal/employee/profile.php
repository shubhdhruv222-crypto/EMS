<?php
require __DIR__.'/../includes/bootstrap.php';require __DIR__.'/../includes/layout.php';$user=require_role('employee');page_start('My Profile',$user);
?><section class="card"><h2 class="card-title">Employee Details</h2><div class="form-grid"><?php foreach (['employee_code'=>'Employee ID','full_name'=>'Full Name','email'=>'Email','phone'=>'Phone','department'=>'Department','designation'=>'Designation','join_date'=>'Joining Date','address'=>'Address','username'=>'Username'] as $k=>$label) input_field($k,$label,$user[$k],'text',false,'readonly'); ?></div></section><?php page_end();
