<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/public_check.php';
require_once 'includes/db.php';
require_once 'includes/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
try {
    if ($search !== '') {
        $stmt = $pdo->prepare('SELECT * FROM services WHERE title LIKE ? OR brief_description LIKE ? ORDER BY id ASC');
        $like = "%$search%";
        $stmt->execute([$like, $like]);
    } else {
        $stmt = $pdo->query('SELECT * FROM services ORDER BY id ASC');
    }
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    $services = [];
}
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Все услуги - ООО «Алгоритм»</title>
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    <?php include 'includes/header.php'; ?>

    <section class="pt-32 py-20">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">Наши услуги</h2>
            <form method="get" class="mb-8 max-w-xl mx-auto relative" id="serviceSearchForm" autocomplete="off" onsubmit="return false;">
                <input type="text" name="search" id="serviceSearchInput" value="<?php echo htmlspecialchars($search); ?>" placeholder="Поиск по услугам..."
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-white dark:border-gray-700 pr-12" />
                <button type="button" id="clearSearchBtn" aria-label="Очистить поиск"
                        class="absolute right-3 top-1/2 -translate-y-1/2 z-20 flex items-center justify-center w-8 h-8 p-0 m-0 bg-gray-200 dark:bg-gray-700 border-0 rounded-full cursor-pointer text-gray-500 hover:text-gray-900 dark:hover:text-white transition hidden"
                        style="pointer-events: auto;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </form>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($services as $service): ?>
                    <div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-6 shadow-sm hover:shadow-md transition-all">
                        <h3 class="text-xl font-bold mb-4 text-gray-900 dark:text-white"><?php echo htmlspecialchars($service['title']); ?></h3>
                        <p class="text-gray-600 dark:text-gray-300 mb-4"><?php echo htmlspecialchars($service['brief_description']); ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo number_format($service['price'], 0, ',', ' '); ?> ₽</span>
                            <span class="text-gray-500 dark:text-gray-400"><?php echo getDaysWordForm($service['duration_days']); ?></span>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2 mt-4">
                            <?php if (isLoggedIn()): ?>
                                <a href="order.php?service_id=<?php echo $service['id']; ?>"
                                   class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg text-center font-semibold hover:bg-green-700 transition-all">
                                    Заказать
                                </a>
                            <?php else: ?>
                                <a href="login.php"
                                   class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg text-center font-semibold hover:bg-green-700 transition-all">
                                    Войти для заказа
                                </a>
                            <?php endif; ?>
                            <button onclick="openServiceModal(<?php echo $service['id']; ?>)"
                                    class="flex-1 px-6 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg font-semibold hover:bg-gray-800 dark:hover:bg-gray-100 transition-all">
                                Подробнее
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <div id="serviceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-900 rounded-lg p-8 max-w-2xl w-full mx-4">
            <div class="flex justify-between items-start mb-6">
                <h3 id="modalTitle" class="text-2xl font-bold"></h3>
                <button onclick="closeServiceModal()" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="modalContent" class="text-gray-600 dark:text-gray-300"></div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        const serviceModal = document.getElementById('serviceModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalContent = document.getElementById('modalContent');
        const services = <?php
            $js_services = [];
            foreach ($services as $service) {
                $js_services[$service['id']] = [
                    'title' => $service['title'],
                    'content' => '<p class="mb-4">'.htmlspecialchars($service['full_description']).'</p>'
                        .'<div class="flex justify-between items-center">'
                        .'<span class="text-2xl font-bold">'.number_format($service['price'], 0, ',', ' ').' ₽</span>'
                        .'<span class="text-gray-500">'.getDaysWordForm($service['duration_days']).'</span>'
                        .'</div>'
                ];
            }
            echo json_encode($js_services, JSON_UNESCAPED_UNICODE);
        ?>;
        function openServiceModal(id) {
            const service = services[id];
            if (!service) return;
            modalTitle.textContent = service.title;
            modalContent.innerHTML = service.content;
            serviceModal.classList.remove('hidden');
            serviceModal.classList.add('flex');
        }
        function closeServiceModal() {
            serviceModal.classList.add('hidden');
            serviceModal.classList.remove('flex');
        }

        let debounceTimer = null;
        const searchInput = document.getElementById('serviceSearchInput');
        const searchForm = document.getElementById('serviceSearchForm');
        const clearBtn = document.getElementById('clearSearchBtn');

        if (searchInput && searchForm) {
            searchInput.addEventListener('input', function () {
                if (debounceTimer) clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    searchForm.submit();
                }, 850);
            });
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        }

        function toggleClearBtn() {
            if (searchInput.value.length > 0) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
        }
        if (searchInput && clearBtn) {
            searchInput.addEventListener('input', toggleClearBtn);
            window.addEventListener('DOMContentLoaded', toggleClearBtn);
            document.addEventListener('DOMContentLoaded', toggleClearBtn);
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                toggleClearBtn();
                searchForm.submit();
            });
        }
    </script>
</body>
</html> 