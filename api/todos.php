<?php
// todos.php
// Basic CRUD for todos table (no auth). Requires db.php with $pdo (PDO instance).
header('Content-Type: application/json; charset=utf-8');

// adjust path to db.php if needed
require_once __DIR__ . '/db.php';

/**
 * Helpers
 */
function bad_request($msg) {
    http_response_code(400);
    echo json_encode(['error' => $msg]);
    exit;
}
function not_found($msg = 'Not found') {
    http_response_code(404);
    echo json_encode(['error' => $msg]);
    exit;
}
function method_not_allowed() {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}
function require_int_param($name) {
    if (!isset($_REQUEST[$name]) || $_REQUEST[$name] === '') return null;
    return filter_var($_REQUEST[$name], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]]);
}
function user_exists(PDO $pdo, $user_id) {
    $st = $pdo->prepare('SELECT 1 FROM users WHERE id = ?');
    $st->execute([$user_id]);
    return (bool)$st->fetchColumn();
}

// get request data (JSON body)
$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? [];

// method + optional id
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

/**
 * GET
 * - GET /todos.php                -> list (with filters)
 * - GET /todos.php?id=123         -> single
 */
if ($method === 'GET') {
    if ($id) {
        $st = $pdo->prepare('SELECT * FROM todos WHERE id = ?');
        $st->execute([$id]);
        $todo = $st->fetch();
        if (!$todo) not_found('Todo not found');
        echo json_encode($todo);
        exit;
    }

    // Listing with optional filters
    $where = [];
    $params = [];

    if (isset($_GET['user_id']) && $_GET['user_id'] !== '') {
        $uid = filter_var($_GET['user_id'], FILTER_VALIDATE_INT);
        if ($uid === false) bad_request('user_id must be an integer');
        $where[] = 'user_id = :uid';
        $params[':uid'] = $uid;
    }

    if (isset($_GET['is_done']) && $_GET['is_done'] !== '') {
        $done = ($_GET['is_done'] === '1' || strtolower($_GET['is_done']) === 'true') ? 1 : 0;
        $where[] = 'is_done = :done';
        $params[':done'] = $done;
    }

    if (!empty($_GET['q'])) {
        $where[] = '(title LIKE :q OR notes LIKE :q)';
        $params[':q'] = '%' . $_GET['q'] . '%';
    }

    if (!empty($_GET['due_before'])) {
        $where[] = 'due_at <= :due_before';
        $params[':due_before'] = $_GET['due_before'];
    }
    if (!empty($_GET['due_after'])) {
        $where[] = 'due_at >= :due_after';
        $params[':due_after'] = $_GET['due_after'];
    }

    $sql = 'SELECT * FROM todos' . (count($where) ? ' WHERE ' . implode(' AND ', $where) : '')
         . ' ORDER BY is_done ASC, due_at IS NULL, due_at ASC, id DESC';

    $st = $pdo->prepare($sql);
    $st->execute($params);
    $rows = $st->fetchAll();
    echo json_encode($rows);
    exit;
}

/**
 * POST (create)
 * Body (JSON): { user_id: int, title: string, notes?: string, is_done?: 0|1, due_at?: "YYYY-MM-DD HH:MM:SS" }
 */
if ($method === 'POST') {
    // validate
    if (empty($input['title'])) bad_request('title is required');
    if (empty($input['user_id'])) bad_request('user_id is required');

    $user_id = filter_var($input['user_id'], FILTER_VALIDATE_INT);
    if ($user_id === false) bad_request('user_id must be an integer');

    if (!user_exists($pdo, $user_id)) bad_request('user_id not found');

    $title = trim($input['title']);
    $notes = isset($input['notes']) ? $input['notes'] : null;
    $is_done = !empty($input['is_done']) ? 1 : 0;
    $due_at = isset($input['due_at']) && $input['due_at'] !== '' ? $input['due_at'] : null;

    $st = $pdo->prepare('INSERT INTO todos (user_id, title, notes, is_done, due_at) VALUES (?, ?, ?, ?, ?)');
    try {
        $st->execute([$user_id, $title, $notes, $is_done, $due_at]);
        http_response_code(201);
        echo json_encode(['id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        bad_request('Could not create todo: ' . $e->getMessage());
    }
    exit;
}

/**
 * PUT (update)
 * PUT /todos.php?id=123
 * Body (JSON): { title?: string, notes?: string|null, is_done?: 0|1, due_at?: string|null, user_id?: int }
 */
if ($method === 'PUT') {
    if (!$id) bad_request('id is required in query string for update');

    // check exists
    $stCheck = $pdo->prepare('SELECT * FROM todos WHERE id = ?');
    $stCheck->execute([$id]);
    $existing = $stCheck->fetch();
    if (!$existing) not_found('Todo not found');

    $fields = [];
    $params = [];

    if (array_key_exists('title', $input)) {
        if ($input['title'] === '' || $input['title'] === null) bad_request('title cannot be empty');
        $fields[] = 'title = ?';
        $params[] = trim($input['title']);
    }
    if (array_key_exists('notes', $input)) {
        $fields[] = 'notes = ?';
        $params[] = $input['notes'];
    }
    if (array_key_exists('is_done', $input)) {
        $fields[] = 'is_done = ?';
        $params[] = !empty($input['is_done']) ? 1 : 0;
    }
    if (array_key_exists('due_at', $input)) {
        // allow null to clear due date
        $fields[] = 'due_at = ?';
        $params[] = $input['due_at'] === null || $input['due_at'] === '' ? null : $input['due_at'];
    }
    if (array_key_exists('user_id', $input)) {
        $new_uid = filter_var($input['user_id'], FILTER_VALIDATE_INT);
        if ($new_uid === false) bad_request('user_id must be an integer');
        if (!user_exists($pdo, $new_uid)) bad_request('user_id not found');
        $fields[] = 'user_id = ?';
        $params[] = $new_uid;
    }

    if (!$fields) {
        // nothing to update
        echo json_encode(['updated' => 0]);
        exit;
    }

    // add updated_at automatically by DB ON UPDATE (but we can still set)
    $sql = 'UPDATE todos SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $params[] = $id;

    $st = $pdo->prepare($sql);
    try {
        $st->execute($params);
        echo json_encode(['updated' => $st->rowCount()]);
    } catch (PDOException $e) {
        bad_request('Could not update todo: ' . $e->getMessage());
    }
    exit;
}

/**
 * DELETE
 * DELETE /todos.php?id=123
 */
if ($method === 'DELETE') {
    if (!$id) bad_request('id is required for delete');

    $st = $pdo->prepare('DELETE FROM todos WHERE id = ?');
    try {
        $st->execute([$id]);
        echo json_encode(['deleted' => $st->rowCount()]);
    } catch (PDOException $e) {
        bad_request('Could not delete todo: ' . $e->getMessage());
    }
    exit;
}

method_not_allowed();
