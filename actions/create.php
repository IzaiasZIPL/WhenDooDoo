<?php

require_once('../database/conn.php');

$title = filter_input(INPUT_POST, 'title');

if ($title){
    $sql = $pdo ->prepare("INSERT INTO task (title) values (:title)");
    $sql ->bindValue(":title", $title);
    $sql ->execute(); 

    header("location: ../index.php");
    exit;
}
else{
    header("location: ../index.php");
    exit;
}