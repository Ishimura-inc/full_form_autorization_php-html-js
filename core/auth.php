<?php
declare(strict_types=1);

/**
 * Защищённая инициализация сессии
 */
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => true,      // ❗ ОБЯЗАТЕЛЬНО HTTPS
    'httponly' => true,
    'samesite' => 'Strict',  // для игры — идеально
]);

session_start();

/**
 * Авторизация
 */
function isAuth(): bool {
    return isset($_SESSION['user_id']) && is_int($_SESSION['user_id']);
}

function requireAuth(): void {
    if (!isAuth()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'  => 'error',
            'message' => 'Not authorized'
        ]);
        exit;
    }
}

/**
 * CSRF
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf']) || time() - ($_SESSION['csrf_time'] ?? 0) > 1800) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_time'] = time();
    }
    return $_SESSION['csrf'];
}

function checkCsrf(string $token): void {
    if (
        empty($_SESSION['csrf']) ||
        !hash_equals($_SESSION['csrf'], $token)
    ) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status'  => 'error',
            'message' => 'CSRF validation failed'
        ]);
        exit;
    }
}