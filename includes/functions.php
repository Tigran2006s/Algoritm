<?php
// Запуск сессии
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Функция проверяет, авторизован ли пользователь
function isLoggedIn() {
    return isset($_SESSION['user_id']); // Если есть user_id в сессии, значит пользователь авторизован
}

// Функция проверяет, является ли пользователь администратором
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Функция требует авторизации, иначе перенаправляет на страницу логина
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /login.php');
        exit();
    }
}

// Функция требует права администратора, иначе перенаправляет на главную страницу
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /index.php');
        exit();
    }
}

// Функция очищает введенные пользователем данные для защиты от инъекций
function sanitizeInput($data) {
    $data = trim($data);            // Удаляем пробелы в начале и конце строки
    $data = stripslashes($data);    // Удаляем экранированные символы
    $data = htmlspecialchars($data);// Преобразуем специальные символы в HTML-сущности
    return $data;
}

// Генерация CSRF-токена для защиты от атак подделки запросов
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Создаем случайный уникальный токен
    }
    return $_SESSION['csrf_token'];
}

// Проверка CSRF-токена на валидность
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Форматирование цены в рублях (с пробелами между тысячами и двумя знаками после запятой)
function formatPrice($price) {
    return number_format($price, 2, '.', ' ') . ' ₽';
}

// Получение информации о конкретной услуге по ее ID
function getServiceById($pdo, $id) {
    $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch(); // Получаем одну запись из базы данных
}

// Получение списка всех услуг с сортировкой по дате создания (от новых к старым)
function getAllServices($pdo) {
    $stmt = $pdo->query('SELECT * FROM services ORDER BY created_at DESC');
    return $stmt->fetchAll(); // Получаем все записи
}

// Получение списка заказов пользователя по его ID
function getUserOrders($pdo, $userId) {
    $stmt = $pdo->prepare('
        SELECT o.*, s.title as service_title 
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC
    ');
    $stmt->execute([$userId]);
    return $stmt->fetchAll(); // Получаем все заказы пользователя
}

// Получение списка отзывов, которые ожидают модерации
function getPendingReviews($pdo) {
    $stmt = $pdo->query('
        SELECT r.*, u.email as user_email
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.status = "pending" 
        ORDER BY r.created_at DESC
    ');
    return $stmt->fetchAll();
}

// Получение списка одобренных отзывов
function getApprovedReviews($pdo) {
    $stmt = $pdo->query('
        SELECT r.*, u.email as user_email
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.status = "approved" 
        ORDER BY r.created_at DESC
    ');
    return $stmt->fetchAll();
}

// Получение списка отклоненных отзывов
function getRejectedReviews($pdo) {
    $stmt = $pdo->query('
        SELECT r.*, u.email as user_email
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        WHERE r.status = "rejected" 
        ORDER BY r.created_at DESC
    ');
    return $stmt->fetchAll();
}

// Получение списка всех пользователей
function getAllUsers($pdo) {
    $stmt = $pdo->query('
        SELECT u.*, 
        (SELECT COUNT(*) FROM orders WHERE user_id = u.id) as orders_count,
        (SELECT COUNT(*) FROM reviews WHERE user_id = u.id) as reviews_count
        FROM users u 
        WHERE u.role != "admin"
        ORDER BY u.created_at DESC
    ');
    return $stmt->fetchAll();
}

// Получение списка всех заказов
function getAllOrders($pdo) {
    $stmt = $pdo->query('
        SELECT o.*, 
        u.email as user_email,
        s.title as service_title,
        s.price as service_price
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN services s ON o.service_id = s.id
        ORDER BY o.created_at DESC
    ');
    return $stmt->fetchAll();
}

// Получение заказов конкретного пользователя
function getUserOrdersForAdmin($pdo, $userId) {
    $stmt = $pdo->prepare('
        SELECT o.*, 
        s.title as service_title,
        s.price as service_price
        FROM orders o
        JOIN services s ON o.service_id = s.id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
    ');
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

// Удаление пользователя и всех связанных данных
function deleteUser($pdo, $userId) {
    try {
        $pdo->beginTransaction();
        
        // Удаляем отзывы пользователя
        $stmt = $pdo->prepare('DELETE FROM reviews WHERE user_id = ?');
        $stmt->execute([$userId]);
        
        // Удаляем заказы пользователя
        $stmt = $pdo->prepare('DELETE FROM orders WHERE user_id = ?');
        $stmt->execute([$userId]);
        
        // Удаляем самого пользователя
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ? AND role != "admin"');
        $stmt->execute([$userId]);
        
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        return false;
    }
}

// Обновление статуса заказа
function updateOrderStatus($pdo, $orderId, $status) {
    $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
    return $stmt->execute([$status, $orderId]);
}

// Удаление заказа
function deleteOrder($pdo, $orderId) {
    $stmt = $pdo->prepare('DELETE FROM orders WHERE id = ?');
    return $stmt->execute([$orderId]);
} 
