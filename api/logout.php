<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/auth.php';

requireAuth();

$data = json_decode(file_get_contents('php://input'), true);
checkCsrf($data['csrf'] ?? '');

session_unset();
session_destroy();

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'ok']);