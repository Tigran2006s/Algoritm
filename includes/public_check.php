<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверяем, не пытается ли пользователь получить доступ к защищенным страницам через URL
$protected_pages = ['account.php', 'order.php', 'admin-panel.php', 'chatbot.php'];
$current_page = basename($_SERVER['PHP_SELF']);

if (in_array($current_page, $protected_pages)) {
    // Если пользователь не авторизован, перенаправляем на страницу входа
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    // Если это админ-панель, проверяем права администратора
    if ($current_page === 'admin-panel.php' && !isset($_SESSION['is_admin'])) {
        header("Location: index.php");
        exit();
    }
}

// Обновляем время последней активности для авторизованных пользователей
if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}
?> 