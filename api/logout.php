<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/auth.php';

requireAuth(); // убедиться, что пользователь авторизован

session_unset();
session_destroy();

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['status' => 'ok']);