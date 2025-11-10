<?php
require 'db.php';

$input = json_decode(file_get_contents('php://input'), true);
$task = isset($input['task']) ? trim($input['task']) : '';
$description = isset($input['description']) ? trim($input['description']) : '';

if ($task === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Task cannot be empty']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO todos (task, description, done) VALUES (:task, :description, 0)");
    $stmt->execute([
        'task' => $task,
        'description' => $description
    ]);
    $id = $pdo->lastInsertId();
    echo json_encode(['success' => true, 'id' => (int)$id]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to add todo']);
}
