<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../core/auth.php';

requireAuth();

/* CSRF */
$data = json_decode(file_get_contents('php://input'), true);
checkCsrf($data['csrf'] ?? '');

/* Очистка данных сессии */
$_SESSION = [];

/* Удаление cookie сессии */
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

/* Уничтожение сессии на сервере */
session_destroy();

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'ok']);
