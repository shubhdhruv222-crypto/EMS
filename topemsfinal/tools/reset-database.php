<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

// Preview by default. Run only after the database owner approves and backs up the target.
$config = require __DIR__ . '/../includes/config.php';
$tables = ['feedback', 'salaries', 'attendance', 'leave_requests', 'users', 'departments'];
try {
    $pdo = new PDO("mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4", $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "Target: {$config['host']}:{$config['port']}/{$config['database']}\n";
    $q = $pdo->query('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()');
    $engines = $q->fetchAll(PDO::FETCH_KEY_PAIR);
    if (array_diff(array_keys($engines), $tables) || array_diff($tables, array_keys($engines))) {
        throw new RuntimeException('Unexpected or missing tables. Review the database before resetting; import upgrade.sql if needed.');
    }
    foreach ($tables as $table) {
        if (strcasecmp((string)$engines[$table], 'InnoDB') !== 0) throw new RuntimeException('All tables must use InnoDB for rollback safety.');
        echo $table . ': ' . $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn() . " rows\n";
    }
    $options = getopt('', ['confirm:']);
    if (($options['confirm'] ?? '') !== 'ERASE:' . $config['database']) {
        echo "Preview only; no records changed. After explicit approval and a verified backup, use --confirm=ERASE:{$config['database']}\n";
        exit(0);
    }
    $pdo->beginTransaction();
    // Lock existing users and use a new session version to invalidate pre-reset logins.
    $versions = $pdo->query('SELECT session_version FROM users FOR UPDATE')->fetchAll(PDO::FETCH_COLUMN);
    $version = $versions ? max(array_map('intval', $versions)) + 1 : 1;
    foreach ($tables as $table) $pdo->exec("DELETE FROM `$table`");
    $q = $pdo->prepare('INSERT INTO users (role,full_name,email,username,password_hash,session_version) VALUES (?,?,?,?,?,?)');
    $q->execute(['admin', 'Administrator', 'admin@gmail.com', 'admin@gmail.com', password_hash('Admin@123', PASSWORD_DEFAULT), $version]);
    $admin = $pdo->query('SELECT * FROM users')->fetchAll(PDO::FETCH_ASSOC);
    if (count($admin) !== 1 || $admin[0]['role'] !== 'admin' || $admin[0]['email'] !== 'admin@gmail.com' || $admin[0]['username'] !== 'admin@gmail.com' || !password_verify('Admin@123', $admin[0]['password_hash'])) {
        throw new RuntimeException('Administrator verification failed.');
    }
    foreach (array_diff($tables, ['users']) as $table) {
        if ((int)$pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn() !== 0) throw new RuntimeException('Empty-table verification failed.');
    }
    $pdo->commit();
    echo "Reset complete: one administrator, no business records.\n";
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    fwrite(STDERR, "Reset stopped: " . $e->getMessage() . "\n");
    exit(1);
}
