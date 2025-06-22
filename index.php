<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/public_check.php';
require_once 'includes/helpers.php';


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $stmt = $pdo->query('SELECT * FROM services ORDER BY id ASC LIMIT 3');
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
    <title>ООО «Алгоритм» - Консалтинговые услуги</title>
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    <?php include 'includes/header.php'; ?>

    <section class="pt-32 pb-20 px-4">
        <div class="container mx-auto text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6">Консалтинг от экспертов</h1>
            <p class="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
                Профессиональные консалтинговые услуги для развития вашего бизнеса. 
                Мы помогаем компаниям достигать новых высот через стратегическое планирование и оптимизацию процессов.
            </p>
            <a href="#services" class="inline-block px-8 py-4 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-all">
                Наши услуги
            </a>
        </div>
    </section>

    <section id="services" class="py-20 bg-gray-50 dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">Наши услуги</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($services as $service): ?>
                <div class="bg-white dark:bg-gray-900 rounded-lg shadow-lg p-6 hover:shadow-xl transition-all">
                    <h3 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($service['title']); ?></h3>
                    <p class="text-gray-600 dark:text-gray-300 mb-4">
                        <?php echo htmlspecialchars($service['brief_description']); ?>
                    </p>
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-xl font-bold">от <?php echo number_format($service['price'], 0, ',', ' '); ?> ₽</span>
                        <span class="text-gray-500"><?php echo getDaysWordForm($service['duration_days']); ?></span>
                    </div>
                    <div class="flex space-x-2">
                        <button class="flex-1 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-all" onclick="openServiceModal(<?php echo $service['id']; ?>)">
                            Подробнее
                        </button>
                        <?php if (isLoggedIn()): ?>
                            <a href="order.php?service_id=<?php echo $service['id']; ?>" class="flex-1 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all text-center">
                                Заказать
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="flex-1 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all text-center">
                                Войти для заказа
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($services)): ?>
            <p class="text-center text-gray-600 dark:text-gray-400">В данный момент услуги недоступны</p>
            <?php else: ?>
            <div class="text-center mt-8">
                <a href="services.php" class="inline-block px-6 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-all">
                    Все услуги
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="about" class="py-20">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">О нас</h2>
            <div class="max-w-4xl mx-auto">
                <div class="prose dark:prose-invert mx-auto">
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-6">
                        ООО "Алгоритм" - это команда профессиональных консультантов с многолетним опытом в различных отраслях бизнеса. 
                        Мы помогаем компаниям достигать новых высот через стратегическое планирование и оптимизацию процессов.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mt-12">
                        <div class="text-center">
                            <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">10+</div>
                            <div class="text-gray-600 dark:text-gray-400">Лет опыта</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">500+</div>
                            <div class="text-gray-600 dark:text-gray-400">Реализованных проектов</div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">98%</div>
                            <div class="text-gray-600 dark:text-gray-400">Довольных клиентов</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="reviews" class="py-20 bg-gray-50 dark:bg-gray-800">
        <div class="container mx-auto px-4">
            <h2 class="text-4xl font-bold text-center mb-12">Отзывы клиентов</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                try {
                    $stmt = $pdo->query('SELECT r.*, u.email as user_email FROM reviews r LEFT JOIN users u ON r.user_id = u.id ORDER BY created_at DESC LIMIT 3');
                    $reviews = $stmt->fetchAll();
                    
                    foreach ($reviews as $review) {
                        echo '<div class="bg-white dark:bg-gray-900 rounded-lg p-6 shadow-sm">';
                        echo '<div class="flex items-center mb-4">';
                        echo '<div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center text-xl font-bold">' . (isset($review['user_email']) ? htmlspecialchars(mb_substr($review['user_email'], 0, 1)) : 'П') . '</div>';
                        echo '<div class="ml-4">';
                        echo '<h3 class="font-bold">' . (isset($review['user_email']) ? htmlspecialchars($review['user_email']) : 'Пользователь') . '</h3>';
                        echo '<p class="text-sm text-gray-500">' . (isset($review['created_at']) ? date('d.m.Y', strtotime($review['created_at'])) : '') . '</p>';
                        echo '</div>';
                        echo '</div>';
                        echo '<p class="text-gray-600 dark:text-gray-400">' . (isset($review['comment']) ? htmlspecialchars($review['comment']) : '') . '</p>';
                        echo '</div>';
                    }
                } catch (PDOException $e) {
                    echo '<p class="text-red-500 text-center">Не удалось загрузить отзывы</p>';
                }
                ?>
                        </div>
            <div class="text-center mt-8">
                <a href="reviews.php" class="inline-block px-6 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-all">
                    Смотреть все отзывы
                </a>
            </div>
        </div>
    </section>

    <!-- FAQ Section (Compact Accordion) -->
    <section id="faq" class="py-20">
        <div class="container mx-auto px-4 max-w-2xl">
            <h2 class="text-4xl font-bold text-center mb-8">Часто задаваемые вопросы</h2>
            <div class="space-y-4">
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <button type="button" class="w-full flex justify-between items-center px-6 py-4 text-lg font-medium focus:outline-none faq-toggle" data-target="faq-answer-0">
                        Как проходит аудит бизнес-процессов?
                        <svg class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="faq-answer-0" class="px-6 pb-4 hidden text-gray-600 dark:text-gray-400">Мы проводим анализ за 3 этапа: сбор данных, анализ процессов, подготовка рекомендаций. На каждом этапе вы получаете промежуточные результаты и можете вносить корректировки.</div>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <button type="button" class="w-full flex justify-between items-center px-6 py-4 text-lg font-medium focus:outline-none faq-toggle" data-target="faq-answer-1">
                        Сколько стоит консультация?
                        <svg class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="faq-answer-1" class="px-6 pb-4 hidden text-gray-600 dark:text-gray-400">Стоимость зависит от выбранной услуги. Базовый аудит начинается от 150 000 ₽. Точную стоимость мы рассчитываем индивидуально после анализа ваших потребностей.</div>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <button type="button" class="w-full flex justify-between items-center px-6 py-4 text-lg font-medium focus:outline-none faq-toggle" data-target="faq-answer-2">
                        Как долго длится проект?
                        <svg class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="faq-answer-2" class="px-6 pb-4 hidden text-gray-600 dark:text-gray-400">Сроки зависят от масштаба проекта и выбранной услуги. В среднем от 14 до 30 дней. Точные сроки обсуждаются на этапе планирования.</div>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <button type="button" class="w-full flex justify-between items-center px-6 py-4 text-lg font-medium focus:outline-none faq-toggle" data-target="faq-answer-3">
                        Какие документы нужны для начала работы?
                        <svg class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="faq-answer-3" class="px-6 pb-4 hidden text-gray-600 dark:text-gray-400">Для начала работы нам потребуется заполненный бриф и основные документы компании. Полный список документов мы предоставляем после первичной консультации.</div>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <button type="button" class="w-full flex justify-between items-center px-6 py-4 text-lg font-medium focus:outline-none faq-toggle" data-target="faq-answer-4">
                        Можно ли получить консультацию онлайн?
                        <svg class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="faq-answer-4" class="px-6 pb-4 hidden text-gray-600 dark:text-gray-400">Да, мы проводим консультации как очно, так и онлайн через видеоконференции. Выбор формата зависит от ваших предпочтений и специфики проекта.</div>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-lg">
                    <button type="button" class="w-full flex justify-between items-center px-6 py-4 text-lg font-medium focus:outline-none faq-toggle" data-target="faq-answer-5">
                        Какие гарантии вы предоставляете?
                        <svg class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div id="faq-answer-5" class="px-6 pb-4 hidden text-gray-600 dark:text-gray-400">Мы гарантируем конфиденциальность информации и качество предоставляемых услуг. Все условия сотрудничества фиксируются в договоре.</div>
                </div>
            </div>
            <div class="text-center mt-8">
                <a href="faq.php" class="inline-block px-6 py-3 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-all">
                    Все вопросы
                </a>
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

    <div class="fixed bottom-4 right-4 z-50">
        <button id="chatButton" class="bg-blue-600 text-white rounded-full p-4 shadow-lg hover:bg-blue-700 transition-all">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
        </button>
        <div id="chatWindow" class="hidden fixed bottom-20 right-4 w-96 h-96 bg-white dark:bg-gray-800 rounded-lg shadow-xl flex flex-col">
            <div class="p-4 border-b dark:border-gray-700 flex justify-between items-center">
                <h3 class="font-bold">Чат поддержки</h3>
                <button id="closeChat" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-4">
                <div class="flex items-start">
                    <div class="bg-blue-100 dark:bg-blue-900 rounded-lg p-3 max-w-[80%]">
                        <p class="text-sm">Здравствуйте! Я бот поддержки ООО "Алгоритм". Чем могу помочь?</p>
                    </div>
                </div>
            </div>
            <div class="p-4 border-t dark:border-gray-700">
                <form id="chatForm" class="flex space-x-2">
                    <input type="text" id="chatInput" class="flex-1 rounded-lg border dark:border-gray-700 dark:bg-gray-900 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Введите сообщение...">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all">
                        Отправить
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>

        // Аккордеон FAQ
        document.querySelectorAll('.faq-toggle').forEach(button => {
            button.addEventListener('click', () => {
                const targetId = button.getAttribute('data-target');
                const answer = document.getElementById(targetId);
                const icon = button.querySelector('svg');

                answer.classList.toggle('hidden');
                icon.classList.toggle('rotate-180');
            });
        });

        // Модальное окно услуги
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

        function toggleAdditionalServices() {
            const additionalServices = document.getElementById('additionalServices');
            const showMoreBtn = document.getElementById('showMoreBtn');
            
            if (additionalServices.classList.contains('hidden')) {
                additionalServices.classList.remove('hidden');
                showMoreBtn.textContent = 'Скрыть';
            } else {
                additionalServices.classList.add('hidden');
                showMoreBtn.textContent = 'Показать еще';
            }
        };

        const chatButton = document.getElementById('chatButton');
        const chatWindow = document.getElementById('chatWindow');
        const closeChat = document.getElementById('closeChat');
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const chatMessages = document.getElementById('chatMessages');

        chatButton.addEventListener('click', () => {
            chatWindow.classList.remove('hidden');
        });

        closeChat.addEventListener('click', () => {
            chatWindow.classList.add('hidden');
        });

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = chatInput.value.trim();
            if (!message) return;

            // Добавляем сообщение пользователя
            addMessage(message, 'user');
            chatInput.value = '';

            try {
                const baseUrl = window.location.origin;
                const chatUrl = `${baseUrl}/OOO_Algoritm_2/chatbot.php`;
                console.log('Base URL:', baseUrl);
                console.log('Chat URL:', chatUrl);
                console.log('Отправка запроса:', message);
                
                const response = await fetch(chatUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ message }),
                });

                console.log('Статус ответа:', response.status);
                console.log('Заголовки ответа:', response.headers);
                
                const responseText = await response.text();
                console.log('Текст ответа:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Ошибка парсинга JSON:', e);
                    throw new Error('Неверный формат ответа от сервера');
                }
                
                if (data.reply) {
                    addMessage(data.reply, 'bot');
                } else {
                    console.error('Ошибка в ответе:', data);
                    addMessage('Извините, произошла ошибка при обработке запроса.', 'bot');
                }
            } catch (error) {
                console.error('Ошибка:', error);
                addMessage('Извините, произошла ошибка. Попробуйте позже.', 'bot');
            }
        });

        function addMessage(text, sender) {
            const messageDiv = document.createElement('div');
            messageDiv.className = 'flex items-start ' + (sender === 'user' ? 'justify-end' : '');
            
            const messageContent = document.createElement('div');
            messageContent.className = sender === 'user' 
                ? 'bg-blue-600 text-white rounded-lg p-3 max-w-[80%]'
                : 'bg-blue-100 dark:bg-blue-900 rounded-lg p-3 max-w-[80%]';
            
            const messageText = document.createElement('p');
            messageText.className = 'text-sm';
            messageText.textContent = text;
            
            messageContent.appendChild(messageText);
            messageDiv.appendChild(messageContent);
            chatMessages.appendChild(messageDiv);
            
            // Прокручиваем к последнему сообщению
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

    </script>
</body>
</html> 