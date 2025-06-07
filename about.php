<?php
require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/public_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ru" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>О нас - ООО «Алгоритм»</title>
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body class="bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
    <?php include 'includes/header.php'; ?>


    <main class="pt-32 pb-20">
        <div class="container mx-auto px-4">
            <h1 class="text-4xl font-bold text-center mb-12">О компании</h1>
            

            <div class="max-w-4xl mx-auto">
                <div class="prose dark:prose-invert mx-auto">
                    <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">
                        ООО "Алгоритм" - это ведущая консалтинговая компания, специализирующаяся на оптимизации бизнес-процессов 
                        и стратегическом планировании. Наша команда состоит из опытных профессионалов с глубокими знаниями в различных 
                        отраслях бизнеса.
                    </p>

                    

                    <h2 class="text-2xl font-bold mb-4">Наша миссия</h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-8">
                        Помогать компаниям достигать максимальной эффективности через внедрение современных методов управления 
                        и оптимизацию бизнес-процессов. Мы стремимся быть надежным партнером для наших клиентов, предоставляя 
                        им инновационные решения и экспертную поддержку.
                    </p>

                    <h2 class="text-2xl font-bold mb-4">Наши преимущества</h2>
                    <ul class="list-disc pl-6 mb-8 space-y-2 text-gray-600 dark:text-gray-300">
                        <li>Индивидуальный подход к каждому клиенту</li>
                        <li>Команда сертифицированных специалистов</li>
                        <li>Использование современных методологий</li>
                        <li>Гарантированный результат</li>
                        <li>Полная конфиденциальность</li>
                    </ul>

                    <h2 class="text-2xl font-bold mb-4">Наша команда</h2>
                    <p class="text-gray-600 dark:text-gray-300 mb-8">
                        В нашей команде работают специалисты с опытом работы в крупнейших консалтинговых компаниях и различных 
                        отраслях бизнеса. Мы постоянно повышаем квалификацию и следим за последними тенденциями в сфере 
                        управленческого консалтинга.
                    </p>
                </div>
                
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 my-12">
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