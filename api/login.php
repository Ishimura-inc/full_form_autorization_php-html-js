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
$email    = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

/* Поиск пользователя */
$stmt = $pdo->prepare(
    'SELECT id, password_hash, failed_logins, last_failed_login
     FROM users
     WHERE email = ?'
);

$stmt->execute([$email]);
$user = $stmt->fetch();

if (
    $user &&
    $user['failed_logins'] >= 5 &&
    $user['last_failed_login'] !== null &&
    strtotime($user['last_failed_login']) > time() - 900
) {
    http_response_code(429);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Too many attempts, try later'
    ]);
    exit;
}



/* Проверка пароля и успешный логин */
if ($user && password_verify($password, $user['password_hash'])) {
    // ✅ Логин успешен

    // Перегенерировать ID сессии, чтобы предотвратить фиксацию
    session_regenerate_id(true);

    // Сохраняем user_id в сессии
    $_SESSION['user_id'] = (int)$user['id'];

    // Можно вернуть успешный JSON
    $pdo->prepare(
        'UPDATE users
         SET failed_logins = 0,
             last_failed_login = NULL
         WHERE id = ?'
    )->execute([$user['id']]);

    echo json_encode([
        'status'  => 'ok',
        'message' => 'Logged in'
    ]);
    exit;
}

/* ИСПРАВЛЕНО: увеличение failed_logins при ошибке */
if ($user) {
    $pdo->prepare(
        'UPDATE users
         SET failed_logins = failed_logins + 1,
             last_failed_login = NOW()
         WHERE email = ?'
    )->execute([$email]);
}


/* Если логин неверный */
http_response_code(401);
echo json_encode([
    'status'  => 'error',
    'message' => 'Invalid credentials'
]);
exit;