<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/public_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error_message = 'Пожалуйста, заполните все поля';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                
                if ($user['role'] === 'admin') {
                    header('Location: admin-panel.php');
                } else {
                    header('Location: account.php');
                }
                exit();
            } else {
                $error_message = 'Неверный email или пароль';
            }
        } catch (PDOException $e) {
            $error_message = 'Ошибка при входе. Пожалуйста, попробуйте позже.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - ООО «Алгоритм»</title>
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white flex items-center justify-center min-h-screen">
    <!-- Login Form -->
    <section class="pt-12 pb-20 px-4">
        <div class="container mx-auto max-w-md">
            <h1 class="text-3xl font-bold text-center mb-8">Вход в аккаунт</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 dark:text-gray-300 mb-2">Email</label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-gray-700 dark:text-gray-300 mb-2">Пароль</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <button type="submit" class="w-full py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-all">
                    Войти
                </button>

                <p class="mt-4 text-center text-gray-600 dark:text-gray-400">
                    Нет аккаунта? <a href="register.php" class="text-gray-900 dark:text-white hover:underline">Зарегистрироваться</a>
                </p>
            </form>
        </div>
    </section>
</body>
</html> 