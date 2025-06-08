<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/session_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];


try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: logout.php');
        exit;
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}


try {
    error_log("Fetching orders for user_id: " . $user_id);
    
    $stmt = $pdo->prepare('
        SELECT o.*, s.title as service_title, s.price as service_price
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        WHERE o.user_id = ? 
        ORDER BY o.created_at DESC
    ');
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
    

    error_log("Found orders: " . count($orders));
    
} catch (PDOException $e) {
    error_log("Error fetching orders: " . $e->getMessage());
    $orders = [];
}


$user_reviews = [];
try {
    $stmt = $pdo->prepare('SELECT r.*, s.title as service_title FROM reviews r JOIN services s ON r.service_id = s.id WHERE r.user_id = ? ORDER BY r.created_at DESC');
    $stmt->execute([$user_id]);
    $user_reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $user_reviews = [];
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $order_id = (int)$_POST['order_id'];
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        try {
            $stmt = $pdo->prepare('UPDATE orders SET status = "cancelled" WHERE id = ? AND user_id = ?');
            $stmt->execute([$order_id, $user_id]);
            header('Location: account.php');
            exit();
        } catch (PDOException $e) {
            error_log("Error cancelling order: " . $e->getMessage());
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_review') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $review_id = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
    
    if ($review_id) {

        $stmt = $pdo->prepare("SELECT user_id FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        $review = $stmt->fetch();

        if ($review && $review['user_id'] == $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            if ($stmt->execute([$review_id])) {
                header('Location: account.php');
                exit;
            }
        }
    }
    
    $error = 'Ошибка при удалении отзыва';
}
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Личный кабинет - ООО «Алгоритм»</title>
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">

    <?php include 'includes/header.php'; ?>

    <main class="pt-32 pb-20 px-4">
        <div class="container mx-auto max-w-4xl">
            <h1 class="text-4xl font-bold text-center mb-12">Личный кабинет</h1>


            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-2xl font-bold mb-4">Информация о пользователе</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Email:</span> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><span class="font-medium">Дата регистрации:</span> <?php echo date('d.m.Y', strtotime($user['created_at'])); ?></p>
                </div>
            </div>


            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-2xl font-bold mb-6">Мои заказы</h2>
                <?php if (empty($orders)): ?>
                    <p class="text-gray-600 dark:text-gray-400 text-center">У вас пока нет заказов</p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($orders as $order): ?>
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-bold"><?php echo htmlspecialchars($order['service_title']); ?></h3>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Заказ №<?php echo $order['id']; ?> от <?php echo date('d.m.Y', strtotime($order['created_at'])); ?>
                                        </p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full text-sm <?php
                                        switch($order['status']) {
                                            case 'pending':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'paid':
                                                echo 'bg-blue-100 text-blue-800';
                                                break;
                                            case 'in_progress':
                                                echo 'bg-purple-100 text-purple-800';
                                                break;
                                            case 'completed':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'cancelled':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                    ?>">
                                        <?php
                                            switch($order['status']) {
                                                case 'pending':
                                                    echo 'Ожидает оплаты';
                                                    break;
                                                case 'paid':
                                                    echo 'Оплачен';
                                                    break;
                                                case 'in_progress':
                                                    echo 'В работе';
                                                    break;
                                                case 'completed':
                                                    echo 'Завершен';
                                                    break;
                                                case 'cancelled':
                                                    echo 'Отменен';
                                                    break;
                                                default:
                                                    echo 'Неизвестный статус';
                                            }
                                        ?>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <p class="text-lg font-bold"><?php echo number_format($order['total_price'], 0, ',', ' '); ?> ₽</p>
                                    <div class="flex space-x-2">
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <button type="submit" name="cancel_order" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all">
                                                    Отменить
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>


            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold mb-4">Мои отзывы</h2>
                <?php

                $stmt = $pdo->prepare("SELECT r.*, u.email as user_email 
                                     FROM reviews r 
                                     LEFT JOIN users u ON r.user_id = u.id 
                                     WHERE r.user_id = ? 
                                     ORDER BY r.created_at DESC");
                $stmt->execute([$_SESSION['user_id']]);
                $user_reviews = $stmt->fetchAll();

                if (!empty($user_reviews)): ?>
                    <div class="space-y-4">
                        <?php foreach ($user_reviews as $review): ?>
                            <div class="border dark:border-gray-700 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                            <?php echo htmlspecialchars($review['review_category']); ?>
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            <?php echo date('d.m.Y', strtotime($review['created_at'])); ?>
                                        </span>
                                    </div>
                                    <form action="account.php" method="POST" class="inline">
                                        <input type="hidden" name="action" value="delete_review">
                                        <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300" onclick="return confirm('Вы уверены, что хотите удалить этот отзыв?')">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                                <div class="flex items-center mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg class="w-5 h-5 <?php echo $i <= ($review['rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'; ?>" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                                <p class="text-gray-600 dark:text-gray-400">
                                    <?php echo htmlspecialchars($review['comment']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600 dark:text-gray-400">У вас пока нет отзывов</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>

        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        themeToggle.addEventListener('click', () => {
            html.classList.toggle('dark');
            localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light';
        });
    </script>
</body>
</html> 