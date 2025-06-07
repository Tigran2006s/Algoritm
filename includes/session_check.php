<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    // Если пользователь не авторизован, перенаправляем на страницу входа
    header("Location: login.php");
    exit();
}

// Проверяем, не истекло ли время сессии (например, 30 минут)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    // Если время сессии истекло, уничтожаем сессию и перенаправляем на страницу входа
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

// Обновляем время последней активности
$_SESSION['last_activity'] = time();
?> 