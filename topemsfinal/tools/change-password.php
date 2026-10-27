<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__.'/../includes/bootstrap.php';
$options=getopt('', ['username:', 'password:']);$name=$options['username']??'';$password=$options['password']??'';
if (!$name || strlen($password)<8 || strlen($password)>72) { fwrite(STDERR,"Usage: php tools/change-password.php --username=admin --password=YourNewPassword\nPassword must contain 8–72 bytes.\n");exit(1); }
$q=db()->prepare('UPDATE users SET password_hash=?,session_version=session_version+1 WHERE username=?');$q->execute([password_hash($password,PASSWORD_DEFAULT),$name]);
if (!$q->rowCount()) {fwrite(STDERR,"Username not found.\n");exit(1);}echo "Password updated; existing sessions have been invalidated.\n";
