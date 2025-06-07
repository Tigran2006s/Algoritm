<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/public_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$faqs = [
    [
        'question' => 'Как проходит аудит бизнес-процессов?',
        'answer' => 'Мы проводим анализ за 3 этапа: сбор данных, анализ процессов, подготовка рекомендаций. На каждом этапе вы получаете промежуточные результаты и можете вносить корректировки.'
    ],
    [
        'question' => 'Сколько стоит консультация?',
        'answer' => 'Стоимость зависит от выбранной услуги. Базовый аудит начинается от 150 000 ₽. Точную стоимость мы рассчитываем индивидуально после анализа ваших потребностей.'
    ],
    [
        'question' => 'Как долго длится проект?',
        'answer' => 'Сроки зависят от масштаба проекта и выбранной услуги. В среднем от 14 до 30 дней. Точные сроки обсуждаются на этапе планирования.'
    ],
    [
        'question' => 'Какие документы нужны для начала работы?',
        'answer' => 'Для начала работы нам потребуется заполненный бриф и основные документы компании. Полный список документов мы предоставляем после первичной консультации.'
    ],
    [
        'question' => 'Можно ли получить консультацию онлайн?',
        'answer' => 'Да, мы проводим консультации как очно, так и онлайн через видеоконференции. Выбор формата зависит от ваших предпочтений и специфики проекта.'
    ],
    [
        'question' => 'Какие гарантии вы предоставляете?',
        'answer' => 'Мы гарантируем конфиденциальность информации и качество предоставляемых услуг. Все условия сотрудничества фиксируются в договоре.'
    ]
];
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - ООО «Алгоритм»</title>
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    <?php include 'includes/header.php'; ?>
    <section class="pt-32 py-20">
        <div class="container mx-auto px-4 max-w-3xl">
            <h2 class="text-4xl font-bold text-center mb-12">Часто задаваемые вопросы</h2>
            <div class="space-y-6">
                <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Как начать работу с вами?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Для начала работы вам необходимо выбрать интересующую вас услугу на нашем сайте, оформить заказ и дождаться звонка от нашего менеджера для уточнения деталей.</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Сколько времени занимает реализация проекта?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Сроки реализации проекта зависят от его сложности и объема. Ориентировочные сроки указаны в описании каждой услуги. Точные сроки будут определены после обсуждения всех деталей с менеджером.</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Какие гарантии вы предоставляете?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Мы гарантируем конфиденциальность всей информации, предоставленной клиентом, а также качество предоставляемых услуг. Все работы выполняются в соответствии с требованиями законодательства и стандартами отрасли.</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Как происходит оплата?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Оплата производится по договору, после согласования всех условий и подписания необходимых документов. Возможны различные способы оплаты: безналичный расчет, банковский перевод и др.</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Можно ли получить консультацию онлайн?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Да, мы предоставляем возможность проведения консультаций онлайн через видеосвязь или мессенджеры. Уточните этот момент при оформлении заказа.</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Какие документы нужны для начала работы?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Для начала работы потребуется предоставить основные регистрационные документы вашей компании. Точный перечень документов уточнит менеджер после оформления заказа.</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Как проходит аудит бизнес-процессов?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Аудит включает анализ текущих процессов, выявление узких мест, подготовку рекомендаций по оптимизации и обсуждение их внедрения с руководством компании.</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Сколько стоит консультация?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Стоимость консультации зависит от выбранной услуги и объема работ. Точные цены указаны в разделе "Услуги" на сайте.</p>
                </div>
                <div class="bg-gray-100 dark:bg-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold mb-2 text-gray-900 dark:text-white">Как долго длится проект?</h3>
                    <p class="text-gray-700 dark:text-gray-300">Длительность проекта зависит от его сложности и объема. Ориентировочные сроки указаны в описании услуги, а точные сроки согласуются индивидуально.</p>
                </div>
            </div>
        </div>
    </section>
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
        function toggleFAQ(index) {
            const answer = document.getElementById(`faq-answer-${index}`);
            const icon = document.getElementById(`faq-icon-${index}`);
            answer.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        }
    </script>
</body>
</html> 