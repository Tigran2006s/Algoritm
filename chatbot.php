<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/session_check.php';

// Включаем отображение ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Логируем метод запроса и заголовки
error_log('Request Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Request Headers: ' . print_r(getallheaders(), true));

// Проверяем, является ли запрос AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Проверяем заголовок X-Requested-With
    $headers = getallheaders();
    $isAjax = isset($headers['X-Requested-With']) && $headers['X-Requested-With'] === 'XMLHttpRequest';
    
    if (!$isAjax) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Not an AJAX request']);
        exit;
    }

    // Получаем JSON-данные из запроса
    $json = file_get_contents('php://input');
    error_log('Raw input: ' . $json);
    
    $data = json_decode($json, true);
    error_log('Decoded data: ' . print_r($data, true));

    // Логируем входящие данные
    error_log('Входящие данные: ' . print_r($data, true));

    // Если это AJAX-запрос
    if (isset($data['message'])) {
        $message = trim($data['message']); // Убираем пробелы, сохраняем регистр
        $message_lower = mb_strtolower($message, 'UTF-8'); // Используем mb_strtolower для корректной работы с кириллицей
        
        // Логируем полученное сообщение и его версию в нижнем регистре
        error_log('Получено сообщение: ' . $message);
        error_log('Сообщение в нижнем регистре: ' . $message_lower);
        
        // Простая логика ответов
        $response = '';
        
        // Проверяем точное совпадение для приветствий (в любом регистре)
        $greetings = ['привет', 'здравствуй', 'здравствуйте', 'добрый день', 'доброе утро', 'добрый вечер'];
        if (in_array($message_lower, $greetings)) {
            error_log('Найдено приветствие: ' . $message_lower);
            $response = 'Здравствуйте! Я бот поддержки ООО "Алгоритм". Я могу рассказать о ценах, сроках, контактах, компании, заказах, личном кабинете, отзывах и FAQ. Чем могу помочь?';
        }
        // Проверяем точное совпадение для прощаний (в любом регистре)
        else {
            $farewells = ['пока', 'до свидания', 'спасибо', 'благодарю', 'спасибо большое'];
            if (in_array($message_lower, $farewells)) {
                error_log('Найдено прощание: ' . $message_lower);
                $response = 'Спасибо за обращение! Желаю вам удачи! Если у вас появятся еще вопросы, я всегда готов помочь!';
            }
            // Проверяем остальные варианты (используем нижний регистр для поиска)
            else if (strpos($message_lower, 'цена') !== false || strpos($message_lower, 'стоимость') !== false || strpos($message_lower, 'сколько стоит') !== false) {
                $response = 'Стоимость наших услуг вы можете посмотреть в разделе "Услуги". Каждая услуга имеет свою цену в зависимости от сложности и объема работы.';
            }
            else if (strpos($message_lower, 'срок') !== false || strpos($message_lower, 'долго') !== false || strpos($message_lower, 'время') !== false || strpos($message_lower, 'когда') !== false) {
                $response = 'Сроки выполнения работ зависят от выбранной услуги и сложности проекта. Точные сроки указаны в описании каждой услуги.';
            }
            else if (strpos($message_lower, 'контакт') !== false || strpos($message_lower, 'телефон') !== false || strpos($message_lower, 'email') !== false || strpos($message_lower, 'связаться') !== false) {
                $response = 'Вы можете связаться с нами по телефону: +7 (XXX) XXX-XX-XX или по email: info@algoritm.ru';
            }
            else if (strpos($message_lower, 'компания') !== false || 
                     strpos($message_lower, 'о вас') !== false || 
                     strpos($message_lower, 'кто вы') !== false ||
                     strpos($message_lower, 'о компании') !== false) {
                $response = 'ООО "Алгоритм" - это ведущая консалтинговая компания, специализирующаяся на оптимизации бизнес-процессов и стратегическом планировании.

Наши преимущества:
• Индивидуальный подход к каждому клиенту
• Команда сертифицированных специалистов
• Использование современных методологий
• Гарантированный результат
• Полная конфиденциальность

Наш опыт:
• Более 10 лет на рынке
• Более 500 реализованных проектов
• 98% довольных клиентов

Подробнее о компании вы можете узнать в разделе "О нас" на нашем сайте.';
            }
            else if (strpos($message_lower, 'заказ') !== false || strpos($message_lower, 'заказать') !== false || strpos($message_lower, 'купить') !== false) {
                $response = 'Для оформления заказа выберите интересующую вас услугу в разделе "Услуги" и нажмите кнопку "Заказать". Если у вас возникнут вопросы, я всегда готов помочь!';
            }
            else if (strpos($message_lower, 'личный кабинет') !== false || strpos($message_lower, 'аккаунт') !== false || strpos($message_lower, 'войти') !== false) {
                $response = 'Для входа в личный кабинет перейдите в раздел "Войти" в верхнем меню. Если у вас еще нет аккаунта, вы можете зарегистрироваться.';
            }
            else if (strpos($message_lower, 'отзыв') !== false || strpos($message_lower, 'отзывы') !== false || strpos($message_lower, 'мнение') !== false) {
                $response = 'Отзывы наших клиентов вы можете посмотреть в разделе "Отзывы". Там же вы можете оставить свой отзыв, если уже пользовались нашими услугами.';
            }
            else if (strpos($message_lower, 'faq') !== false || strpos($message_lower, 'вопрос') !== false || strpos($message_lower, 'часто задаваемые') !== false) {
                $response = 'Ответы на часто задаваемые вопросы вы можете найти в разделе "FAQ". Если вы не нашли ответ на свой вопрос, я готов помочь!';
            }
            else {
                error_log('Сообщение не распознано: ' . $message);
                $response = 'Извините, я не совсем понял ваш вопрос. Можете переформулировать или выбрать один из популярных вопросов:
                - О ценах
                - О сроках
                - О контактах
                - О компании
                - О заказе
                - О личном кабинете
                - Об отзывах
                - О FAQ';
            }
        }
        
        // Логируем ответ
        error_log('Отправляем ответ: ' . $response);
        
        // Отправляем ответ в формате JSON
        header('Content-Type: application/json');
        echo json_encode(['reply' => $response]);
        exit;
    }
    
    // Если это не AJAX-запрос с сообщением, возвращаем ошибку
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Если это не POST-запрос, показываем HTML-страницу
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Чат поддержки - ООО «Алгоритм»</title>
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white flex items-center justify-center min-h-screen">
    <?php include 'includes/header.php'; ?>

    <section class="pt-32 pb-20 px-4">
        <!-- Здесь будет содержимое страницы -->
    </section>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 