<?php
// users.php — CRUD para tabela users
// Coloque este arquivo em WhenDooDoo/api/
// NÃO deixe espaços/linhas antes de <?php

// Headers JSON + CORS
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, X-Requested-With');
class foo{
    require_once __DIR__ . '/db.php';
}

// Responder preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// importa conexão PDO ($pdo)

// método HTTP e possíveis parâmetros
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

// ler JSON de entrada (para POST/PUT)
$raw = file_get_contents('php://input');
$input = json_decode($raw, true) ?? [];

// helpers simples
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

/**
 * GET /users.php
 * GET /users.php?id=1
 */
if ($method === 'GET') {
    if ($id) {
        $st = $pdo->prepare('SELECT * FROM users WHERE id = ?');
        $st->execute([$id]);
        $user = $st->fetch();
        if (!$user) not_found('User not found');
        echo json_encode($user);
    } else {
        $st = $pdo->query('SELECT * FROM users ORDER BY id DESC');
        echo json_encode($st->fetchAll());
    }
    exit;
}

/**
 * POST /users.php
 * Body: { "name": "...", "email": "..." }
 */
if ($method === 'POST') {
    if (empty($input['name']) || empty($input['email'])) {
        bad_request('Name and email are required');
    }

    $name = trim($input['name']);
    $email = trim($input['email']);

    $st = $pdo->prepare('INSERT INTO users (name, email) VALUES (?, ?)');
    try {
        $st->execute([$name, $email]);
        http_response_code(201);
        echo json_encode(['id' => $pdo->lastInsertId()]);
    } catch (PDOException $e) {
        bad_request('Could not create user: ' . $e->getMessage());
    }
    exit;
}

/**
 * PUT /users.php?id=1
 * Body: { "name": "...", "email": "..." }
 */
if ($method === 'PUT') {
    if (!$id) bad_request('id is required in query string');

    // verificar se existe
    $st = $pdo->prepare('SELECT id FROM users WHERE id = ?');
    $st->execute([$id]);
    if (!$st->fetch()) not_found('User not found');

    $fields = [];
    $params = [];

    if (array_key_exists('name', $input)) {
        $fields[] = 'name = ?';
        $params[] = trim($input['name']);
    }
    if (array_key_exists('email', $input)) {
        $fields[] = 'email = ?';
        $params[] = trim($input['email']);
    }

    if (!$fields) {
        echo json_encode(['updated' => 0]);
        exit;
    }

    $params[] = $id;
    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
    $st = $pdo->prepare($sql);
    $st->execute($params);

    echo json_encode(['updated' => $st->rowCount()]);
    exit;
}

/**
 * DELETE /users.php?id=1
 */
if ($method === 'DELETE') {
    if (!$id) bad_request('id is required for delete');

    $st = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $st->execute([$id]);
    echo json_encode(['deleted' => $st->rowCount()]);
    exit;
}

method_not_allowed();
