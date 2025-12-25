<?php
declare(strict_types=1);

$envPath = dirname(__DIR__, 2) . '/.env';
$logPath = dirname(__DIR__, 2) . '/.logs/db.log';

if (!is_readable($envPath)) {
    error_log('[DB] .env not readable', 3, $logPath);
    http_response_code(500);
    exit;
}

$env = parse_ini_file($envPath, false, INI_SCANNER_TYPED);

try {
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $env['DB_HOST'],
        $env['DB_NAME']
    );

    $pdo = new PDO(
        $dsn,
        $env['DB_USER'],
        $env['DB_PASS'],
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

} catch (PDOException $e) {
    error_log(
        '[DB] Connection error: ' . $e->getMessage() . PHP_EOL,
        3,
        $logPath
    );
    http_response_code(500);
    exit;
}