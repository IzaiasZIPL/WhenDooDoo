<?php
require 'db.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? (int)$input['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid id']);
    exit;
}

// You can update 'done' and/or 'task'
$updates = [];
$params = ['id' => $id];

if (isset($input['done'])) {
    $updates[] = 'done = :done';
    $params['done'] = $input['done'] ? 1 : 0;
}
if (isset($input['task'])) {
    $updates[] = 'task = :task';
    $params['task'] = trim($input['task']);
}

if (empty($updates)) {
    http_response_code(400);
    echo json_encode(['error' => 'Nothing to update']);
    exit;
}

$sql = "UPDATE todos SET " . implode(', ', $updates) . " WHERE id = :id";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update todo']);
}
