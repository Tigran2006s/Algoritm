<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/session_check.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

requireAdmin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Ошибка безопасности. Пожалуйста, попробуйте снова.';
    } else {
        switch ($_POST['action']) {
            case 'add_service':
                $title = sanitizeInput($_POST['title'] ?? '');
                $brief_description = sanitizeInput($_POST['brief_description'] ?? '');
                $full_description = sanitizeInput($_POST['full_description'] ?? '');
                $price = (float)($_POST['price'] ?? 0);
                $duration_days = (int)($_POST['duration_days'] ?? 0);

                if (empty($title) || empty($brief_description) || empty($full_description) || $price <= 0 || $duration_days <= 0) {
                    $error = 'Пожалуйста, заполните все поля корректно';
                } else {
                    $stmt = $pdo->prepare('INSERT INTO services (title, brief_description, full_description, price, duration_days) VALUES (?, ?, ?, ?, ?)');
                    try {
                        $stmt->execute([$title, $brief_description, $full_description, $price, $duration_days]);
                        $success = 'Услуга успешно добавлена';
                    } catch (PDOException $e) {
                        $error = 'Ошибка при добавлении услуги';
                    }
                }
                break;

            case 'edit_service':
                $id = (int)($_POST['id'] ?? 0);
                $title = sanitizeInput($_POST['title'] ?? '');
                $brief_description = sanitizeInput($_POST['brief_description'] ?? '');
                $full_description = sanitizeInput($_POST['full_description'] ?? '');
                $price = (float)($_POST['price'] ?? 0);
                $duration_days = (int)($_POST['duration_days'] ?? 0);

                if (empty($title) || empty($brief_description) || empty($full_description) || $price <= 0 || $duration_days <= 0) {
                    $error = 'Пожалуйста, заполните все поля корректно';
                } else {
                    $stmt = $pdo->prepare('UPDATE services SET title = ?, brief_description = ?, full_description = ?, price = ?, duration_days = ? WHERE id = ?');
                    try {
                        $stmt->execute([$title, $brief_description, $full_description, $price, $duration_days, $id]);
                        $success = 'Услуга успешно обновлена';
                    } catch (PDOException $e) {
                        $error = 'Ошибка при обновлении услуги';
                    }
                }
                break;

            case 'delete_service':
                $id = (int)($_POST['id'] ?? 0);
                $stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
                try {
                    $stmt->execute([$id]);
                    $success = 'Услуга успешно удалена';
                } catch (PDOException $e) {
                    $error = 'Ошибка при удалении услуги';
                }
                break;

            case 'moderate_review':
                if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
                    $error = 'Ошибка безопасности: недействительный токен';
                    break;
                }
                
                $id = (int)($_POST['id'] ?? 0);
                $action = $_POST['moderation_action'] ?? '';
                
                if (in_array($action, ['approve', 'reject'])) {
                    $stmt = $pdo->prepare('UPDATE reviews SET status = ? WHERE id = ?');
                    try {
                        $stmt->execute([$action === 'approve' ? 'approved' : 'rejected', $id]);
                        $success = 'Отзыв успешно ' . ($action === 'approve' ? 'одобрен' : 'отклонен');
                    } catch (PDOException $e) {
                        error_log('Ошибка при модерации отзыва: ' . $e->getMessage());
                        $error = 'Ошибка при модерации отзыва';
                    }
                } else {
                    $error = 'Неверное действие модерации';
                }
                break;

            case 'delete_review':
                if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
                    $error = 'Ошибка безопасности: недействительный токен';
                    break;
                }
                
                $id = (int)($_POST['id'] ?? 0);
                $stmt = $pdo->prepare('DELETE FROM reviews WHERE id = ?');
                try {
                    $stmt->execute([$id]);
                    $success = 'Отзыв успешно удален';
                } catch (PDOException $e) {
                    error_log('Ошибка при удалении отзыва: ' . $e->getMessage());
                    $error = 'Ошибка при удалении отзыва';
                }
                break;

            case 'delete_user':
                $id = (int)($_POST['id'] ?? 0);
                if (deleteUser($pdo, $id)) {
                    $success = 'Пользователь и все его данные успешно удалены';
                } else {
                    $error = 'Ошибка при удалении пользователя';
                }
                break;

            case 'update_order_status':
                $id = (int)($_POST['id'] ?? 0);
                $status = sanitizeInput($_POST['status'] ?? '');
                if (in_array($status, ['pending', 'in_progress', 'completed', 'cancelled'])) {
                    if (updateOrderStatus($pdo, $id, $status)) {
                        $success = 'Статус заказа успешно обновлен';
                    } else {
                        $error = 'Ошибка при обновлении статуса заказа';
                    }
                }
                break;

            case 'delete_order':
                $id = (int)($_POST['id'] ?? 0);
                if (deleteOrder($pdo, $id)) {
                    $success = 'Заказ успешно удален';
                } else {
                    $error = 'Ошибка при удалении заказа';
                }
                break;
        }
    }
}


$services = getAllServices($pdo);
$pending_reviews = getPendingReviews($pdo);
$approved_reviews = getApprovedReviews($pdo);
$rejected_reviews = getRejectedReviews($pdo);
$users = getAllUsers($pdo);
$orders = getAllOrders($pdo);
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора - ООО «Алгоритм»</title>
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">

    <?php include 'includes/header.php'; ?>

    <main class="pt-24 pb-20 px-4 lg:pt-32">
        <div class="container mx-auto">
            <h1 class="text-3xl lg:text-4xl font-bold mb-8">Панель администратора</h1>

            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo $success; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>

            <!-- Услуги -->
            <section id="services" class="mb-12">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Управление услугами</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Название</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Цена</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Срок</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($services as $service): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($service['title']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo number_format($service['price'], 0, ',', ' '); ?> ₽</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo getDaysWordForm($service['duration_days']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="editService(<?php echo htmlspecialchars(json_encode($service)); ?>)" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">Редактировать</button>
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="action" value="delete_service">
                                            <input type="hidden" name="id" value="<?php echo $service['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" onclick="return confirm('Вы уверены, что хотите удалить эту услугу?')">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <button onclick="showAddServiceModal()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all">Добавить услугу</button>
                </div>
            </section>

            <!-- Отзывы -->
            <section id="reviews" class="mb-12">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Модерация отзывов</h2>
                    <div class="space-y-6">
                        <!-- Ожидающие модерации -->
                        <div>
                            <h3 class="text-xl font-semibold mb-4">Ожидающие модерации</h3>
                            <div class="space-y-4">
                                <?php foreach ($pending_reviews as $review): ?>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($review['user_email']); ?></p>
                                            <p class="mt-2"><?php echo htmlspecialchars($review['comment']); ?></p>
                                        </div>
                                        <div class="flex flex-col space-y-2 ml-4">
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="moderate_review">
                                                <input type="hidden" name="id" value="<?php echo $review['id']; ?>">
                                                <input type="hidden" name="moderation_action" value="approve">
                                                <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700 transition-all">Одобрить</button>
                                            </form>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                                <input type="hidden" name="action" value="moderate_review">
                                                <input type="hidden" name="id" value="<?php echo $review['id']; ?>">
                                                <input type="hidden" name="moderation_action" value="reject">
                                                <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-all">Отклонить</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Одобренные отзывы -->
                        <div>
                            <h3 class="text-xl font-semibold mb-4">Одобренные отзывы</h3>
                            <div class="space-y-4">
                                <?php foreach ($approved_reviews as $review): ?>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($review['user_email']); ?></p>
                                            <p class="mt-2"><?php echo htmlspecialchars($review['comment']); ?></p>
                                        </div>
                                        <form method="POST" class="mt-2 md:mt-0">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="action" value="delete_review">
                                            <input type="hidden" name="id" value="<?php echo $review['id']; ?>">
                                            <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-all" onclick="return confirm('Вы уверены, что хотите удалить этот отзыв?')">Удалить</button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Отклоненные отзывы -->
                        <div>
                            <h3 class="text-xl font-semibold mb-4">Отклоненные отзывы</h3>
                            <div class="space-y-4">
                                <?php foreach ($rejected_reviews as $review): ?>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($review['user_email']); ?></p>
                                            <p class="mt-2"><?php echo htmlspecialchars($review['comment']); ?></p>
                                        </div>
                                        <form method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="action" value="delete_review">
                                            <input type="hidden" name="id" value="<?php echo $review['id']; ?>">
                                            <button type="submit" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-all" onclick="return confirm('Вы уверены, что хотите удалить этот отзыв?')">Удалить</button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Пользователи -->
            <section id="users" class="mb-12">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Управление пользователями</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Роль</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            <?php
                                                if ($user['role'] === 'user') {
                                                    echo 'Пользователь';
                                                } else {
                                                    echo htmlspecialchars($user['role']); // Сохраняем другие роли как есть, если они есть
                                                }
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Заказы -->
            <section id="orders" class="mb-12">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                    <h2 class="text-2xl font-bold mb-6">Управление заказами</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Пользователь</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Услуга</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Статус</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Действия</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white"><?php echo htmlspecialchars($order['user_email']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($order['service_title']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            <?php
                                                $statusTranslations = [
                                                    'pending' => 'Ожидает',
                                                    'in_progress' => 'В работе',
                                                    'completed' => 'Завершен',
                                                    'cancelled' => 'Отменен',
                                                ];
                                                echo $statusTranslations[$order['status']] ?? htmlspecialchars($order['status']);
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="action" value="update_order_status">
                                            <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                            <select name="status" onchange="this.form.submit()" class="text-sm border rounded px-2 py-1 dark:bg-gray-700 dark:border-gray-600">
                                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Ожидает</option>
                                                <option value="in_progress" <?php echo $order['status'] === 'in_progress' ? 'selected' : ''; ?>>В работе</option>
                                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Завершен</option>
                                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Отменен</option>
                                            </select>
                                        </form>
                                        <form method="POST" class="inline ml-2">
                                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                            <input type="hidden" name="action" value="delete_order">
                                            <input type="hidden" name="id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" onclick="return confirm('Вы уверены, что хотите удалить этот заказ?')">Удалить</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Модальное окно добавления/редактирования услуги -->
    <div id="serviceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 rounded-lg p-6 max-w-2xl w-full mx-4">
            <div class="flex justify-between items-start mb-6">
                <h3 id="modalTitle" class="text-2xl font-bold"></h3>
                <button onclick="closeServiceModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form id="serviceForm" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="add_service">
                <input type="hidden" name="id" id="serviceId">
                
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Название услуги</label>
                    <input type="text" id="title" name="title" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                
                <div>
                    <label for="brief_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Краткое описание</label>
                    <textarea id="brief_description" name="brief_description" required rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                </div>
                
                <div>
                    <label for="full_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Полное описание</label>
                    <textarea id="full_description" name="full_description" required rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Цена (₽)</label>
                        <input type="number" id="price" name="price" required min="0" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    
                    <div>
                        <label for="duration_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Срок (дней)</label>
                        <input type="number" id="duration_days" name="duration_days" required min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeServiceModal()" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all">Отмена</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-all">Сохранить</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Мобильное меню
        const mobileMenuButton = document.getElementById('mobileMenuButton');
        const closeMobileMenu = document.getElementById('closeMobileMenu');
        const mobileMenu = document.getElementById('mobileMenu');
        const overlay = document.createElement('div');
        overlay.className = 'mobile-menu-overlay fixed inset-0 bg-black bg-opacity-50 z-30';
        document.body.appendChild(overlay);

        function toggleMobileMenu() {
            mobileMenu.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        }

        function closeMenu() {
            mobileMenu.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        mobileMenuButton.addEventListener('click', toggleMobileMenu);
        closeMobileMenu.addEventListener('click', closeMenu);
        overlay.addEventListener('click', closeMenu);

        // Закрытие меню при изменении ориентации устройства
        window.addEventListener('orientationchange', function() {
            if (window.innerWidth > 1024) {
                closeMenu();
            }
        });

        // Закрытие меню при изменении размера окна
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024) {
                closeMenu();
            }
        });

        // Плавная прокрутка к секциям
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                    if (window.innerWidth <= 1024) {
                        closeMenu();
                    }
                }
            });
        });

        // Тема
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

        // Модальное окно услуг
        const serviceModal = document.getElementById('serviceModal');
        const modalTitle = document.getElementById('modalTitle');
        const serviceForm = document.getElementById('serviceForm');
        const serviceId = document.getElementById('serviceId');

        function showAddServiceModal() {
            modalTitle.textContent = 'Добавить услугу';
            serviceForm.reset();
            serviceForm.action.value = 'add_service';
            serviceId.value = '';
            serviceModal.classList.remove('hidden');
            serviceModal.classList.add('flex');
        }

        function editService(service) {
            modalTitle.textContent = 'Редактировать услугу';
            serviceForm.action.value = 'edit_service';
            serviceId.value = service.id;
            document.getElementById('title').value = service.title;
            document.getElementById('brief_description').value = service.brief_description;
            document.getElementById('full_description').value = service.full_description;
            document.getElementById('price').value = service.price;
            document.getElementById('duration_days').value = service.duration_days;
            serviceModal.classList.remove('hidden');
            serviceModal.classList.add('flex');
        }

        function closeServiceModal() {
            serviceModal.classList.add('hidden');
            serviceModal.classList.remove('flex');
        }

        // Обработка отправки формы услуги
        serviceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                document.documentElement.innerHTML = html;
                // Перезагрузка страницы для обновления данных
                window.location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при сохранении услуги');
            });
        });

        // Закрытие модального окна при клике вне его
        serviceModal.addEventListener('click', function(e) {
            if (e.target === serviceModal) {
                closeServiceModal();
            }
        });

        // Закрытие модального окна при нажатии Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !serviceModal.classList.contains('hidden')) {
                closeServiceModal();
            }
        });
    </script>
</body>
</html> 