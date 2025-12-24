<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$accept = $_SERVER['HTTP_ACCEPT'] ?? '';

if (strpos($accept, 'application/json') === false) {
    http_response_code(406);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error', 'message' => 'JSON only']);
    exit;
}

require_once __DIR__ . '/../core/auth.php';

header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'csrf' => csrfToken()
]);
