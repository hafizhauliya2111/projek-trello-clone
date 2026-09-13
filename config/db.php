<?php

$host = 'localhost';
$dbname = 'trello_db';
$username = 'root';
$password = '';

try{
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    error_log("Terjadi kesalahan pada sistem: " . $e->getMessage());
    echo "koneksi database gagal";
    exit();
}