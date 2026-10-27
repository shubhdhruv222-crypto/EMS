<?php
declare(strict_types=1);
ini_set('session.use_strict_mode', '1');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
session_start();
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
function db(): PDO {
    static $pdo;
    if (!$pdo) {
        $c = require __DIR__ . '/config.php';
        $pdo = new PDO("mysql:host={$c['host']};port={$c['port']};dbname={$c['database']};charset=utf8mb4", $c['username'], $c['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]);
    }
    return $pdo;
}
set_exception_handler(function (Throwable $e): void {
    error_log((string)$e);
    http_response_code(500);
    echo '<!DOCTYPE html><html lang="en"><meta charset="utf-8"><title>EMS setup required</title><h1>Unable to complete the request</h1><p>Check that MySQL is running, database/schema.sql has been imported, and includes/config.php matches your database settings. See SETUP.md for instructions.</p></html>';
});
function h($value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function url(string $path): string {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
    $base = dirname($script);
    if (in_array(basename($base), ['admin', 'employee'], true)) $base = dirname($base);
    return rtrim($base, '/') . '/' . ltrim($path, '/');
}
function redirect(string $path): void { header('Location: ' . url($path), true, 303); exit; }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . h(csrf()) . '">'; }
function check_csrf(): void {
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals(csrf(), $_POST['csrf'])) {
        http_response_code(403); exit('Invalid form token. Reload the page and try again.');
    }
}
function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $s = db()->prepare('SELECT * FROM users WHERE id = ?'); $s->execute([$_SESSION['user_id']]); $u = $s->fetch();
    if (!$u || (int)$u['session_version'] !== (int)($_SESSION['session_version'] ?? 0)) {
        unset($_SESSION['user_id'], $_SESSION['session_version']); return null;
    }
    return $u;
}
function require_role(string $role): array {
    $u = current_user();
    if (!$u) redirect('login.php');
    if ($u['role'] !== $role) { http_response_code(403); exit('<h1>Access denied</h1><p>This account cannot access this area. Log out and sign in again.</p>'); }
    return $u;
}
function flash(string $message): void { $_SESSION['flash'] = $message; }
function show_flash(): void { if (isset($_SESSION['flash'])) { echo '<p class="notice" role="status">' . h($_SESSION['flash']) . '</p>'; unset($_SESSION['flash']); } }
function post(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
function record_id($value): int { $n = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]); if ($n === false) { http_response_code(400); exit('Invalid record ID.'); } return $n; }
function missing(): void { http_response_code(404); exit('Record not found or not accessible to this account.'); }
function text_value(string $key, int $max, bool $required = true): string {
    $v = trim(is_string($_POST[$key] ?? null) ? $_POST[$key] : '');
    if (($required && $v === '') || strlen($v) > $max) throw new InvalidArgumentException("Enter a valid $key (maximum $max bytes).");
    return $v;
}
function money($amount): string { return '₹' . number_format((float)$amount, 2); }
function cents(string $key): int {
    $raw = is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
    if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $raw)) throw new InvalidArgumentException('Enter non-negative amounts with at most two decimal places.');
    $parts = explode('.', $raw); return (int)$parts[0] * 100 + (int)str_pad($parts[1] ?? '', 2, '0');
}
function decimal_amount(int $cents): string { return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100); }
