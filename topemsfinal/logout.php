<?php
require __DIR__ . '/includes/bootstrap.php';
if (!post()) { http_response_code(405); header('Allow: POST'); exit('Use the Logout button.'); }
check_csrf();
$_SESSION = [];
$p = session_get_cookie_params();
setcookie(session_name(), '', ['expires'=>time()-3600, 'path'=>$p['path'], 'domain'=>$p['domain'], 'secure'=>$p['secure'], 'httponly'=>true, 'samesite'=>'Lax']);
session_destroy();
redirect('login.php');
