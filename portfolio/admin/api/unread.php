<?php
require_once __DIR__ . '/../../includes/functions.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

try {
    $count = (int)db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
} catch (Throwable $e) {
    $count = 0;
}

echo json_encode(['count' => $count]);