<?php

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=algoritm_db_777;charset=utf8mb4',
        'root',
        'root',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . $e->getMessage());
} 