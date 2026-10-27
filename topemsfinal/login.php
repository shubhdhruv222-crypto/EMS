<?php
require __DIR__ . '/includes/bootstrap.php';
$existing = current_user();
if ($existing) redirect($existing['role'].'/dashboard.php');
$error = '';
$username = '';
if (post()) {
    check_csrf();
    $username = trim(is_string($_POST['username'] ?? null) ? $_POST['username'] : '');
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    // Username is unique across all users, so the role (admin/employee) comes from the account itself.
    $stmt = db()->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]); $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION = ['user_id'=>(int)$user['id'], 'session_version'=>(int)$user['session_version']];
        redirect($user['role'].'/dashboard.php');
    }
    $error = 'Invalid username or password.';
}
?><!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Login | EMS</title><link rel="stylesheet" href="css/style.css"></head><body class="login-body"><main class="login-container"><div class="login-logo">👥</div><h1>Employee Management System</h1><p class="login-subtitle">Sign in with your username and password</p><form method="post"><?=csrf_field()?><div class="form-group"><label for="username">Username</label><input id="username" name="username" autocomplete="username" value="<?=h($username)?>" required maxlength="80" autofocus></div><div class="form-group"><label for="password">Password</label><div class="password-box"><input type="password" id="password" name="password" autocomplete="current-password" required><button type="button" id="showPassword" class="password-toggle" aria-label="Show or hide password">👁</button></div></div><?php if ($error): ?><p class="login-error" style="display:block" role="alert"><?=h($error)?></p><?php endif; ?><button class="login-button" type="submit">Login</button></form></main><script src="js/main.js"></script></body></html>
