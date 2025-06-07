<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Determine if the current page is the admin panel
$isAdminPanel = (basename($_SERVER['PHP_SELF']) === 'admin-panel.php');

// Ensure generateCSRFToken is available if needed
if (!$isAdminPanel || (isset($_SESSION['user_id']) && $isAdminPanel)) {
    require_once 'includes/functions.php';
}

?>
<header class="fixed w-full bg-white dark:bg-gray-900 shadow-sm z-50">
    <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
        <!-- Logo -->
        <a href="<?php echo $isAdminPanel ? 'admin-panel.php' : 'index.php'; ?>" class="text-2xl font-bold"><?php echo $isAdminPanel ? 'Алгоритм - Админ-панель' : 'Алгоритм'; ?></a>
        
        <?php if (!$isAdminPanel): // Show regular navigation and right section only on non-admin pages ?>
            <!-- Desktop Navigation Links -->
            <div class="hidden md:flex flex-grow justify-center items-center space-x-8">
                <a href="services.php" class="hover:text-gray-600 dark:hover:text-gray-300 transition-all">Услуги</a>
                <a href="about.php" class="hover:text-gray-600 dark:hover:text-gray-300 transition-all">О нас</a>
                <a href="reviews.php" class="hover:text-gray-600 dark:hover:bg-gray-300 transition-all">Отзывы</a>
                <a href="faq.php" class="hover:text-gray-600 dark:hover:text-gray-300 transition-all">FAQ</a>
            </div>

            <!-- Desktop Right Section: Theme Toggle + Auth/Account/Logout -->
            <div class="hidden md:flex items-center space-x-4">
                 <!-- Theme Toggle -->
                 <button id="themeToggle" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path class="dark:hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        <path class="hidden dark:block" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="account.php" class="inline-block px-4 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-lg hover:bg-gray-800 dark:hover:bg-gray-100 transition-all">Личный кабинет</a>
                    <form method="POST" action="logout.php" class="inline">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all">Выход</button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="px-4 py-2 rounded-lg bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 transition-all">Войти</a>
                    <a href="register.php" class="px-4 py-2 rounded-lg border border-gray-900 dark:border-white text-gray-900 dark:text-white hover:bg-gray-900 hover:text-white dark:hover:bg-white dark:hover:text-gray-900 transition-all">Регистрация</a>
                <?php endif; ?>
            </div>
        <?php else: // On admin panel, show only Logout button ?>
            <div class="flex items-center space-x-4">
                <form method="POST" action="logout.php" class="inline">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-all">Выход</button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (!$isAdminPanel): // Show mobile menu button and theme toggle only on non-admin pages ?>
        <!-- Mobile menu button and theme toggle (visible on small screens) -->
        <div class="flex items-center space-x-4 md:hidden">
             <!-- Theme Toggle for mobile header -->
             <button id="themeToggleMobile" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path class="dark:hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    <path class="hidden dark:block" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>
            <button id="mobileMenuButton" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
        <?php endif; ?>

    </nav>
    
    <!-- Mobile menu structure - exists on all pages but hidden on admin panel via CSS/JS -->
    <div id="mobileMenu" class="mobile-menu fixed inset-y-0 left-0 w-64 bg-white dark:bg-gray-900 shadow-lg z-40 transform -translate-x-full transition-transform duration-300 ease-in-out md:hidden <?php echo $isAdminPanel ? 'hidden' : ''; ?>">
        <div class="p-4">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold">Меню</h2>
                <button id="closeMobileMenu" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <nav class="space-y-2">
                <a href="index.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">Главная</a>
                <a href="services.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">Услуги</a>
                <a href="about.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">О нас</a>
                <a href="reviews.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">Отзывы</a>
                <a href="faq.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">FAQ</a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="account.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">Личный кабинет</a>
                    <form method="POST" action="logout.php" class="block">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        <button type="submit" class="w-full text-left px-4 py-2 rounded-lg text-red-600 hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-900 transition-all">Выход</button>
                    </form>
                <?php else: ?>
                    <a href="login.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">Войти</a>
                    <a href="register.php" class="block px-4 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">Регистрация</a>
                <?php endif; ?>

                
            </nav>
        </div>
    </div>
    <div id="mobileMenuOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden <?php echo $isAdminPanel ? 'hidden' : ''; ?>"></div>

</header>

<script>
    // Theme toggle functionality
    // Use a single event listener for the desktop theme toggle as it has a unique ID
    const themeToggleDesktop = document.getElementById('themeToggle');
    const html = document.documentElement;

    // Apply initial theme based on localStorage or system preference
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        html.classList.add('dark');
    } else {
        html.classList.remove('dark');
    }

    // Function to toggle theme and save preference
    function toggleTheme() {
        html.classList.toggle('dark');
        localStorage.theme = html.classList.contains('dark') ? 'dark' : 'light';
    }

    // Add event listener to the desktop toggle button if it exists
    if (themeToggleDesktop) {
        themeToggleDesktop.addEventListener('click', toggleTheme);
    }

    // Mobile menu functionality
    const mobileMenuButton = document.getElementById('mobileMenuButton');
    const closeMobileMenu = document.getElementById('closeMobileMenu');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuOverlay = document.getElementById('mobileMenuOverlay');
    const themeToggleMobile = document.getElementById('themeToggleMobile'); // Get mobile theme toggle button

    // Only initialize mobile menu and its elements if they exist (i.e., not on admin panel)
    if (mobileMenuButton && closeMobileMenu && mobileMenu && mobileMenuOverlay) {
        function toggleMobileMenu() {
            mobileMenu.classList.toggle('-translate-x-full');
            mobileMenu.classList.toggle('translate-x-0');
            mobileMenuOverlay.classList.toggle('hidden');
            document.body.style.overflow = mobileMenu.classList.contains('translate-x-0') ? 'hidden' : '';
        }

        function closeMenu() {
            mobileMenu.classList.remove('translate-x-0');
            mobileMenu.classList.add('-translate-x-full');
            mobileMenuOverlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        mobileMenuButton.addEventListener('click', toggleMobileMenu);
        closeMobileMenu.addEventListener('click', closeMenu);
        mobileMenuOverlay.addEventListener('click', closeMenu);

        // Close menu on link click (for smooth scrolling) - adjusted to find links inside the mobile menu
        document.querySelectorAll('#mobileMenu a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                // No preventDefault here, allow normal link behavior
                closeMenu();
            });
        });

        // Close menu on orientation change and window resize for larger screens
        window.addEventListener('orientationchange', function() {
            if (window.innerWidth >= 768) { // md breakpoint
                closeMenu();
            }
        });

        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) { // md breakpoint
                closeMenu();
            }
        });

        // Initialize mobile theme toggle listener here as it's inside the mobile menu div
        if (themeToggleMobile) {
            themeToggleMobile.addEventListener('click', toggleTheme);
        }
    }

</script> 