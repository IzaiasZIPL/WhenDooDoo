<?php

$hostname = 'localhost';
$database= 'todo';
$username = 'root';
$password = '';

try{
    $pdo = new PDO('mysql:host=localhost;dbname=todo;charse=utf8', "root", "");
} catch (PDOException $e) {
    echo 'Erro: ' . $e->getMessage();
}

