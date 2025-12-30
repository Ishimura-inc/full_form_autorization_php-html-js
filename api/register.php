<?php
declare(strict_types=1);

/* Только POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

/* Только JSON */
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== 0) {
    http_response_code(415);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'JSON only']);
    exit;
}

require_once __DIR__ . '/../core/auth.php';
require_once __DIR__ . '/../core/db.php';

header('Content-Type: application/json; charset=utf-8');

/* Чтение JSON */
$data = json_decode(file_get_contents('php://input'), true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

/* CSRF */
checkCsrf($data['csrf'] ?? '');

/* Данные */
$nickname        = trim((string)($data['nickname'] ?? ''));
$email = trim(mb_strtolower((string)($data['email'] ?? '')));
$password = $data['password'] ?? '';
$passwordRepeat  = (string)($data['password_repeat'] ?? '');

/* Rate limit по IP */
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$stmt = $pdo->prepare('SELECT attempts, last_attempt FROM register_attempts WHERE ip = ?');
$stmt->execute([$ip]);
$row = $stmt->fetch();
if ($row && $row['attempts'] >= 3 && strtotime($row['last_attempt']) > time() - 3600) {
    http_response_code(429);
    echo json_encode(['status' => 'error', 'message' => 'Too many registrations from this IP']);
    exit;
}


/* Валидация */
if ($nickname === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Nickname required']);
    exit;
}

if (mb_strlen($nickname) < 3 || mb_strlen($nickname) > 32) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid nickname length']);
    exit;
}

/* (опционально) допустимые символы */
if (!preg_match('/^[\p{L}\p{N}_\- ]+$/u', $nickname)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid nickname characters']);
    exit;
}

/* Запрещённые никнеймы */
$forbiddenNicknames = [
    'admin',
    'administrator',
    'moderator',
    'root',
    'system'
];

/* Проверка на запрещённые имена (без учёта регистра) */
if (in_array(mb_strtolower($nickname), array_map('mb_strtolower', $forbiddenNicknames), true)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'This nickname is not allowed'
    ]);
    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email']);
    exit;
}

if (strlen($email) > 255) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Email too long']);
    exit;
}

if (strlen($password) < 6 || strlen($password) > 64) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid password length']);
    exit;
}

if ($password !== $passwordRepeat) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Passwords do not match']);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

/* Очистка пароля из памяти */
$password = $passwordRepeat = null;




/* Запись в БД */
$stmt = $pdo->prepare(
    'INSERT INTO users (nickname, email, password_hash, failed_logins, last_failed_login) VALUES (?, ?, ?, 0, NULL)'
);

try {
    $stmt->execute([$nickname, $email, $hash]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') { // duplicate key
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'User already exists']);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
    }
    exit;
}

/* Обновление таблицы register_attempts */
if ($row) {
    $stmt = $pdo->prepare('UPDATE register_attempts SET attempts = attempts + 1, last_attempt = NOW() WHERE ip = ?');
    $stmt->execute([$ip]);
} else {
    $stmt = $pdo->prepare('INSERT INTO register_attempts (ip, attempts, last_attempt) VALUES (?, 1, NOW())');
    $stmt->execute([$ip]);
}


$userId = (int)$pdo->lastInsertId();
session_regenerate_id(true);
$_SESSION['user_id'] = $userId;

/* Регенерация CSRF */
unset($_SESSION['csrf'], $_SESSION['csrf_time']);

echo json_encode(['status' => 'ok']);