<?php
define('DB_HOST', 'localhost:/Applications/MAMP/tmp/mysql/mysql.sock');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'algoritm_db_777');

try {
    $pdo = new PDO(
        "mysql:unix_socket=/Applications/MAMP/tmp/mysql/mysql.sock;dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
} 