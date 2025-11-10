<?php
require 'db.php';

try {
    $pdo->exec("TRUNCATE TABLE todos");
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to delete all todos']);
}
