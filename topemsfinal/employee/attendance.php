<?php
require __DIR__.'/../includes/modules.php';$user=require_role('employee');page_start('My Attendance',$user);require __DIR__.'/../includes/attendance-list.php';page_end();
