<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/session_check.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error_message = '';
$success_message = '';

$service_id = filter_input(INPUT_GET, 'service_id', FILTER_VALIDATE_INT);
if (!$service_id) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
    $stmt->execute([$service_id]);
    $service = $stmt->fetch();

    if (!$service) {
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $error_message = 'Ошибка при получении данных услуги';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!isLoggedIn()) {
            throw new Exception('Пользователь не авторизован');
        }

        $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ?');
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();

        if (!$service) {
            throw new Exception('Услуга не найдена');
        }

        $stmt = $pdo->prepare('SELECT id FROM users WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception('Пользователь не найден');
        }

        $requirements = isset($_POST['requirements']) ? trim($_POST['requirements']) : null;
        $deadline = isset($_POST['deadline']) ? $_POST['deadline'] : null;

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('
                INSERT INTO orders (user_id, service_id, status, total_price, requirements, deadline)
                VALUES (?, ?, "pending", ?, ?, ?)
            ');
            $result = $stmt->execute([
                $_SESSION['user_id'],
                $service_id,
                $service['price'],
                $requirements,
                $deadline
            ]);

            if (!$result) {
                throw new Exception('Ошибка при создании заказа');
            }

            $order_id = $pdo->lastInsertId();

            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ?');
            $stmt->execute([$order_id]);
            $created_order = $stmt->fetch();

            if (!$created_order) {
                throw new Exception('Заказ не был создан');
            }

            $pdo->commit();

            header('Location: account.php');
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    } catch (Exception $e) {
        $error_message = 'Ошибка при создании заказа: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Размещение заказа - ООО «Алгоритм»</title>
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    <?php include 'includes/header.php'; ?>

    <!-- Order Section -->
    <section class="pt-32 py-20">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">Оформление заказа</h2>
            <div class="max-w-4xl mx-auto">
                <?php if (isset($_GET['service_id'])): ?>
                    <?php
                    try {
                        $stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
                        $stmt->execute([$_GET['service_id']]);
                        $service = $stmt->fetch();

                        if ($service):
                    ?>
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                            <div class="mb-8">
                                <h3 class="text-2xl font-semibold mb-4"><?php echo htmlspecialchars($service['title']); ?></h3>
                                <p class="text-gray-600 dark:text-gray-400 mb-4">
                                    <?php echo htmlspecialchars($service['full_description']); ?>
                                </p>
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-gray-600 dark:text-gray-400">
                                            Срок выполнения: <?php echo getDaysWordForm($service['duration_days']); ?>
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-bold"><?php echo number_format($service['price'], 2); ?> ₽</p>
                                    </div>
                                </div>
                            </div>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form action="order.php?service_id=<?php echo $service['id']; ?>" method="POST" class="space-y-6">
                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                    <div>
                                        <label for="requirements" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Требования к проекту
                                        </label>
                                        <textarea
                                            id="requirements"
                                            name="requirements"
                                            rows="4"
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                            required
                                        ></textarea>
                                    </div>
                                    <div>
                                        <label for="deadline" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Желаемый срок выполнения
                                        </label>
                                        <input
                                            type="date"
                                            id="deadline"
                                            name="deadline"
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                            required
                                        >
                                    </div>
                                    <button
                                        type="submit"
                                        class="w-full bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors"
                                    >
                                        Оформить заказ
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="text-center">
                                    <p class="text-gray-600 dark:text-gray-400 mb-4">Для оформления заказа необходимо войти в систему</p>
                                    <a href="login.php" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors">
                                        Войти
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php
                        else:
                            echo '<p class="text-center text-red-500">Услуга не найдена</p>';
                        endif;
                    } catch (PDOException $e) {
                        echo '<p class="text-center text-red-500">Ошибка при загрузке данных услуги</p>';
                    }
                    ?>
                <?php else: ?>
                    <p class="text-center text-gray-600 dark:text-gray-400">Не выбрана услуга для заказа</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Theme Toggle
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