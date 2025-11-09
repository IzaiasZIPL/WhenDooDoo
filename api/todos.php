<?php
// api/todos.php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

session_start();

require __DIR__ . '/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$session_id = session_id();

function json($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
    if ($method === 'GET') {
        $stmt = $pdo->prepare('SELECT id, title, notes, is_done, created_at, updated_at FROM todos WHERE session_id = ? ORDER BY created_at DESC');
        $stmt->execute([$session_id]);
        json(['todos' => $stmt->fetchAll()]);
    }

    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $title = trim($input['title'] ?? '');
        $notes = $input['notes'] ?? null;
        if ($title === '') json(['error' => 'Title required'], 422);

        $stmt = $pdo->prepare('INSERT INTO todos (session_id, title, notes) VALUES (?, ?, ?)');
        $stmt->execute([$session_id, $title, $notes]);
        $id = (int)$pdo->lastInsertId();

        $stmt = $pdo->prepare('SELECT * FROM todos WHERE id = ? AND session_id = ?');
        $stmt->execute([$id, $session_id]);
        json(['todo' => $stmt->fetch()], 201);
    }

    if ($method === 'PUT') {
        if (!$id) json(['error' => 'Missing id'], 400);
        $input = json_decode(file_get_contents('php://input'), true);

        $fields = [];
        $params = [];

        if (isset($input['title'])) {
            $fields[] = 'title = ?';
            $params[] = $input['title'];
        }
        if (isset($input['notes'])) {
            $fields[] = 'notes = ?';
            $params[] = $input['notes'];
        }
        if (isset($input['is_done'])) {
            $fields[] = 'is_done = ?';
            $params[] = $input['is_done'] ? 1 : 0;
        }

        if (empty($fields)) json(['error' => 'Nothing to update'], 400);

        $params[] = $id;
        $params[] = $session_id;

        $sql = 'UPDATE todos SET ' . implode(', ', $fields) . ' WHERE id = ? AND session_id = ?';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $stmt = $pdo->prepare('SELECT * FROM todos WHERE id = ? AND session_id = ?');
        $stmt->execute([$id, $session_id]);
        json(['todo' => $stmt->fetch()]);
    }

    if ($method === 'DELETE') {
        if ($id) {
            $stmt = $pdo->prepare('DELETE FROM todos WHERE id = ? AND session_id = ?');
            $stmt->execute([$id, $session_id]);
            json(['deleted' => (bool)$stmt->rowCount()]);
        } else {
            $stmt = $pdo->prepare('DELETE FROM todos WHERE session_id = ?');
            $stmt->execute([$session_id]);
            json(['deleted_all' => true]);
        }
    }

    json(['error' => 'Method not allowed'], 405);
} catch (PDOException $e) {
    json(['error' => 'DB error'], 500);
}
