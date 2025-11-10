<?php
// api/db.php
<<<<<<< HEAD
header('Content-Type: application/json; charset=utf-8');
// Allow requests from your frontend origin in production instead of '*'
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, X-Requested-With");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$host = '127.0.0.1';
$db   = 'todo_app';
$user = 'root';
$pass = ''; // change to your DB password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
=======
declare(strict_types=1);

$DB_HOST = '127.0.0.1';
$DB_NAME = 'whendoodoo';
$DB_USER = 'seu_usuario';
$DB_PASS = 'sua_senha';
$DB_CHAR = 'utf8mb4';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHAR";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
>>>>>>> 947cc790eee72ef41e77e42225ccb5fd7d0e64af
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
<<<<<<< HEAD
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
=======
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'DB connection failed']);
>>>>>>> 947cc790eee72ef41e77e42225ccb5fd7d0e64af
    exit;
}
