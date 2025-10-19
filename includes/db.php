<?php
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "skol_portal";

try {
    $conn = new PDO("mysql:host={$dbHost};dbname={$dbName}", $dbUser, $dbPass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    http_response_code(503);
    die("Database Connection Failed: " . $e->getMessage());
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>