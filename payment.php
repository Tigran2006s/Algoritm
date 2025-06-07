<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Проверка авторизации
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Проверка наличия ID заказа
if (!isset($_GET['order_id'])) {
    header('Location: account.php');
    exit;
}

$order_id = (int)$_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Получение информации о заказе
$stmt = $pdo->prepare('SELECT o.*, s.title FROM orders o 
                      JOIN services s ON o.service_id = s.id 
                      WHERE o.id = ? AND o.user_id = ? AND o.status = "pending"');
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    error_log('Заказ не найден: order_id=' . $order_id . ', user_id=' . $user_id);
    header('Location: account.php');
    exit;
}

// Проверяем наличие необходимых данных
if (!isset($order['total_price']) || !isset($order['title'])) {
    error_log('Отсутствуют необходимые данные заказа: ' . print_r($order, true));
    die('Ошибка: отсутствуют необходимые данные заказа');
}

// Создание платежа через ЮKassa
// TODO: Замените эти значения на реальные данные из личного кабинета ЮKassa
// https://yookassa.ru/joinups
$shop_id = 'YOUR_SHOP_ID'; // Замените на ваш ID магазина из личного кабинета ЮKassa
$secret_key = 'YOUR_SECRET_KEY'; // Замените на ваш секретный ключ из личного кабинета ЮKassa

// Проверяем, что данные не тестовые
if ($shop_id === 'YOUR_SHOP_ID' || $secret_key === 'YOUR_SECRET_KEY') {
    error_log('Ошибка: не настроены данные ЮKassa');
    die('Ошибка: не настроены данные платежной системы. Пожалуйста, обратитесь к администратору.');
}

// Форматируем сумму для ЮKassa (должна быть в копейках)
$amount = number_format($order['total_price'], 2, '.', '');

$payment_data = [
    'amount' => [
        'value' => $amount,
        'currency' => 'RUB'
    ],
    'confirmation' => [
        'type' => 'redirect',
        'return_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/payment-success.php?order_id=' . $order_id
    ],
    'capture' => true,
    'description' => 'Оплата заказа #' . $order['id'] . ' - ' . $order['title'],
    'metadata' => [
        'order_id' => $order['id']
    ]
];

error_log('Данные для создания платежа: ' . print_r($payment_data, true));

$ch = curl_init('https://api.yookassa.ru/v3/payments');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payment_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Idempotence-Key: ' . uniqid(),
    'Authorization: Basic ' . base64_encode($shop_id . ':' . $secret_key)
]);

// Включаем отладку CURL
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Получаем информацию об ошибке CURL
if (curl_errno($ch)) {
    error_log('Ошибка CURL: ' . curl_error($ch));
}

// Получаем подробную информацию о запросе
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
error_log('Подробная информация о запросе: ' . $verboseLog);

curl_close($ch);

if ($http_code !== 200) {
    $error = json_decode($response, true);
    error_log('Ошибка ЮKassa: ' . print_r($error, true));
    error_log('HTTP код: ' . $http_code);
    error_log('Данные платежа: ' . print_r($payment_data, true));
    die('Ошибка при создании платежа. Код ошибки: ' . $http_code . '. Пожалуйста, попробуйте позже или обратитесь в поддержку.');
}

$payment = json_decode($response, true);
if (!$payment || !isset($payment['confirmation']['confirmation_url'])) {
    error_log('Неверный ответ от ЮKassa: ' . print_r($payment, true));
    die('Ошибка при создании платежа. Неверный ответ от платежной системы. Пожалуйста, попробуйте позже или обратитесь в поддержку.');
}

$payment_url = $payment['confirmation']['confirmation_url'];

// Сохранение ID платежа в базе
$stmt = $pdo->prepare("UPDATE orders SET payment_id = ? WHERE id = ?");
$stmt->execute([$payment['id'], $order_id]);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Оплата заказа - Консультационные услуги</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Оплата заказа #<?php echo $order['id']; ?></h1>
            
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">Способы оплаты</h2>
                <div class="space-y-4">
                    <div class="border rounded-lg p-4 bg-green-50">
                        <h3 class="font-semibold text-green-700">💳 Банковская карта (самый простой способ)</h3>
                        <p class="text-gray-600">Оплата любой банковской картой Visa, Mastercard или МИР</p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <h3 class="font-semibold">📱 СБП (Система быстрых платежей)</h3>
                        <p class="text-gray-600">Оплата через мобильное приложение вашего банка</p>
                    </div>
                    <div class="border rounded-lg p-4">
                        <h3 class="font-semibold">🏦 Банковский перевод</h3>
                        <p class="text-gray-600">Оплата через интернет-банк или мобильное приложение</p>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-4">Детали заказа</h2>
                <div class="space-y-2">
                    <p><span class="font-semibold">Сумма к оплате:</span> <?php echo number_format($order['total_price'], 0, ',', ' '); ?> ₽</p>
                    <p><span class="font-semibold">Услуга:</span> <?php echo htmlspecialchars($order['title']); ?></p>
                </div>
            </div>

            <div class="text-center">
                <a href="<?php echo $payment_url; ?>" class="inline-block bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition-all">
                    Перейти к оплате
                </a>
                <p class="mt-4 text-sm text-gray-500">После оплаты вы будете автоматически перенаправлены обратно на сайт</p>
            </div>
        </div>
    </div>
</body>
</html> 