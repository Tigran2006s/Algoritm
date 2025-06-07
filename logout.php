<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        die('Ошибка безопасности');
    }
    
    // Очищаем все данные сессии
    session_unset();
    session_destroy();
    
    // Перенаправляем на главную страницу
    header('Location: index.php');
    exit();
} else {
    // Если запрос не POST, перенаправляем на главную страницу
    header('Location: index.php');
    exit();
}
?>