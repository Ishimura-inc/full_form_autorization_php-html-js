<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../../../core/db.php';

header('Content-Type: application/json; charset=utf-8');

/*
 | ЕДИНАЯ ТОЧКА ИСТИНЫ:
 | кто текущий пользователь
*/

if (!isAuth()) {
    echo json_encode([
        'status' => 'error',
        'auth' => false,
        'message' => 'Not authorized'
    ]);
    exit;
}

// Пользователь авторизован
$stmt = $pdo->prepare('SELECT nickname FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'auth' => false,
        'message' => 'User not found'
    ]);
    exit;
}

echo json_encode([
    'status' => 'ok',
    'auth' => true,
    'data' => [
        'nickname' => $user['nickname']
    ]
]);