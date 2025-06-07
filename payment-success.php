<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Проверяем авторизацию
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

try {
    // Получаем информацию о заказе
    $stmt = $pdo->prepare('SELECT o.*, s.title FROM orders o 
                          JOIN services s ON o.service_id = s.id 
                          WHERE o.id = ? AND o.user_id = ?');
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();

    if (!$order) {
        throw new Exception('Заказ не найден');
    }

    // Проверяем статус платежа в ЮKassa
    $shop_id = 'YOUR_SHOP_ID';
    $secret_key = 'YOUR_SECRET_KEY';

    $ch = curl_init('https://api.yookassa.ru/v3/payments/' . $order['payment_id']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode($shop_id . ':' . $secret_key)
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        $payment = json_decode($response, true);
        
        if ($payment['status'] === 'succeeded') {
            // Обновляем статус заказа
            $stmt = $pdo->prepare('UPDATE orders SET status = "paid" WHERE id = ?');
            $stmt->execute([$order_id]);
            
            $_SESSION['success'] = 'Оплата прошла успешно!';
        } else {
            $_SESSION['error'] = 'Платеж не был завершен';
        }
    } else {
        $_SESSION['error'] = 'Ошибка при проверке статуса платежа';
    }
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

header('Location: account.php');
exit; 