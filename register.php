<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/public_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($email) || empty($password) || empty($password_confirm)) {
        $error_message = 'Пожалуйста, заполните все поля';
    } elseif ($password !== $password_confirm) {
        $error_message = 'Пароли не совпадают';
    } elseif (strlen($password) < 8) {
        $error_message = 'Пароль должен содержать минимум 8 символов';
    } else {
        try {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error_message = 'Этот email уже зарегистрирован';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (email, password, role) VALUES (?, ?, ?)');
                $stmt->execute([$email, $hashed_password, 'user']);
                
                $success_message = 'Регистрация успешна! Теперь вы можете войти.';
                
                $email = '';
                $password = '';
                $password_confirm = '';
            }
        } catch (PDOException $e) {
            $error_message = 'Ошибка при регистрации. Пожалуйста, попробуйте позже.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация - ООО «Алгоритм»</title>
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white flex items-center justify-center min-h-screen">
    <section class="pt-12 pb-20 px-4">
        <div class="container mx-auto max-w-md">
            <h1 class="text-3xl font-bold text-center mb-8">Регистрация</h1>
            
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

            <form method="POST" class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 dark:text-gray-300 mb-2">Email</label>
                    <input type="email" id="email" name="email" required
                           value="<?php echo htmlspecialchars($email ?? ''); ?>"
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 dark:text-gray-300 mb-2">Пароль</label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <div class="mb-6">
                    <label for="password_confirm" class="block text-gray-700 dark:text-gray-300 mb-2">Подтверждение пароля</label>
                    <input type="password" id="password_confirm" name="password_confirm" required
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                </div>

                <button type="submit" class="w-full py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-all">
                    Зарегистрироваться
                </button>

                <p class="mt-4 text-center text-gray-600 dark:text-gray-400">
                    Уже есть аккаунт? <a href="login.php" class="text-gray-900 dark:text-white hover:underline">Войти</a>
                </p>
            </form>
        </div>
    </section>
</body>
</html> 