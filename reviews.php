<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/public_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error_message = '';
$success_message = '';

try {
    $stmt = $pdo->query("SELECT r.*, u.email as user_email 
                         FROM reviews r 
                         LEFT JOIN users u ON r.user_id = u.id 
                         ORDER BY r.created_at DESC");
    $reviews = $stmt->fetchAll();
} catch (PDOException $e) {
    $reviews = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_review') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $comment = trim($_POST['comment'] ?? '');
    $review_category = trim($_POST['review_category'] ?? 'Общий отзыв');
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);

    if (empty($comment) || !$rating) {
        $error = 'Пожалуйста, заполните все поля';
    } else {
        $stmt = $pdo->prepare("INSERT INTO reviews (user_id, review_category, rating, comment, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
        if ($stmt->execute([$_SESSION['user_id'], $review_category, $rating, $comment])) {
            $success_message = 'Отзыв успешно добавлен и ожидает модерации';
        } else {
            $error_message = 'Ошибка при добавлении отзыва';
        }
    }
}

$services = getAllServices($pdo);
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отзывы - ООО «Алгоритм»</title>
    <link href="public/css/style.css" rel="stylesheet">
</head>

<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white min-h-screen flex flex-col">
    <?php include 'includes/header.php'; ?>

    <main class="flex-grow pt-32 pb-20">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl font-bold text-center mb-12">Отзывы наших клиентов</h1>
            <?php if (isLoggedIn()): ?>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold mb-4">Оставить отзыв</h2>
                    <?php if (!empty($error_message)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($success_message)): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>
                    <form action="reviews.php" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add_review">
                        <div>
                            <label for="review_category" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Категория отзыва</label>
                            <select name="review_category" id="review_category" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="Общий отзыв">Общий отзыв</option>
                                <option value="Бухгалтерские услуги">Бухгалтерские услуги</option>
                                <option value="Налоговое консультирование">Налоговое консультирование</option>
                                <option value="Аудит">Аудит</option>
                                <option value="Бизнес-консалтинг">Бизнес-консалтинг</option>
                                <option value="Другое">Другое</option>
                            </select>
                        </div>
                        <div>
                            <label for="rating" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Оценка</label>
                            <select id="rating" name="rating" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Выберите оценку</option>
                                <option value="5">5 — Отлично</option>
                                <option value="4">4 — Хорошо</option>
                                <option value="3">3 — Нормально</option>
                                <option value="2">2 — Плохо</option>
                                <option value="1">1 — Очень плохо</option>
                            </select>
                        </div>
                        <div>
                            <label for="comment" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Ваш отзыв</label>
                            <textarea name="comment" id="comment" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            Оставить отзыв
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-6 mb-8 text-center">
                    <p class="mb-4 text-gray-700 dark:text-gray-300">Чтобы оставить отзыв, пожалуйста, <a href="login.php" class="text-blue-600 dark:text-blue-400 underline">войдите в аккаунт</a>.</p>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($reviews)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($reviews as $review): ?>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold">
                                        <?php echo strtoupper(substr($review['user_email'], 0, 1)); ?>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center space-x-2">
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white truncate">
                                            <?php 
                                            $email = $review['user_email'];
                                            if (strlen($email) > 20) {
                                                $username = explode('@', $email)[0];
                                                $domain = explode('@', $email)[1];
                                                if (strlen($username) > 10) {
                                                    $username = substr($username, 0, 10) . '...';
                                                }
                                                echo htmlspecialchars($username . '@' . $domain);
                                            } else {
                                                echo htmlspecialchars($email);
                                            }
                                            ?>
                                        </h3>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <?php echo date('d.m.Y', strtotime($review['created_at'])); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg class="w-5 h-5 <?php echo $i <= ($review['rating'] ?? 0) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'; ?>" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    <?php endfor; ?>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-200">
                                    <?php echo htmlspecialchars($review['review_category']); ?>
                                </span>
                            </div>
                            <p class="mt-4 text-gray-600 dark:text-gray-400">
                                <?php echo isset($review['comment']) ? htmlspecialchars($review['comment']) : ''; ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-center text-gray-600 dark:text-gray-400">Пока нет отзывов</p>
            <?php endif; ?>
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