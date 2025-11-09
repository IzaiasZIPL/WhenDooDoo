<?php
// db.php — database connection using PDO (MySQL)

// database credentials
$host = 'localhost';      // usually 'localhost' when using phpMyAdmin locally
$dbname = 'todo_app';     // same name as in your init.sql
$username = 'root';       // default in XAMPP/MAMP/WAMP (you can change if needed)
$password = '';           // leave empty if you didn’t set one in phpMyAdmin

// optional: better error handling and defaults
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // use real prepared statements
];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        $options
    );
} catch (PDOException $e) {
    // if connection fails, show a clean error (useful during dev)
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
