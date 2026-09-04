<?php

if (!defined('DB_NAME')) {
    define('DB_NAME', 'elderly_care');
}

$host    = '127.0.0.1';
$dbName  = DB_NAME;
$dbUser  = 'root';
$dbPass  = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, $options);
} catch (PDOException $e) {
    error_log('DB error: ' . $e->getMessage());
    http_response_code(500);
    exit('Something went wrong connecting to the database. Try again later.');
}
