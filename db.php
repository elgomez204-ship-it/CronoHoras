<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'cronohoras');
define('DB_USER', 'juan@empresa.com');
define('DB_PASS', '123456789');

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die('Database connection error: ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}
