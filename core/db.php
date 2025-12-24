<?php
declare(strict_types=1);

$envPath = dirname(__DIR__, 2) . '/.env';

if (!is_readable($envPath)) {
    throw new RuntimeException('.env file not found or not readable');
}

$env = parse_ini_file($envPath, false, INI_SCANNER_TYPED);

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=utf8mb4',
    $env['DB_HOST'],
    $env['DB_NAME']
);

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$pdo = new PDO(
    $dsn,
    $env['DB_USER'],
    $env['DB_PASS'],
    $options
);