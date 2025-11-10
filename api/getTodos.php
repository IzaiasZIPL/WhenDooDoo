<?php
require 'db.php';

try {
    $stmt = $pdo->query("SELECT id, task, description, done, created_at FROM todos ORDER BY created_at DESC");
    $todos = $stmt->fetchAll();
    echo json_encode($todos);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch todos']);
}
