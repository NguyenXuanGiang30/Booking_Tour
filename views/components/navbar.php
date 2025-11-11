<?php
require_once __DIR__ . '/../../includes/i18n.php';

// Determine current page based on REQUEST_URI
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH);
$requestPath = rtrim($requestPath, '/');

// Remove base path if exists (for subdirectories like /booking-tour/)
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
if ($basePath) {
    $basePath = rtrim($basePath, '/');
    if ($basePath && strpos($requestPath, $basePath) === 0) {
        $requestPath = substr($requestPath, strlen($basePath));
    }
}
$requestPath = rtrim($requestPath, '/') ?: '/';

// Determine current page
$currentPage = 'home';
if (strpos($requestPath, '/tours') === 0 || $requestPath === '/tours') {
    $currentPage = 'tours';
} elseif (strpos($requestPath, '/about') === 0 || $requestPath === '/about') {
    $currentPage = 'about';
} elseif (strpos($requestPath, '/contact') === 0 || $requestPath === '/contact') {
    $currentPage = 'contact';
} elseif ($requestPath === '/' || empty($requestPath)) {
    $currentPage = 'home';
}

$isLoggedIn = Auth::isLoggedIn();
$user = Auth::getUser();
$currentLang = i18n::getCurrentLang();
?>
<nav class="fixed top-0 left-0 right-0 bg-white shadow-sm z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <a href="<?= url('/') ?>" class="flex items-center space-x-2 cursor-pointer">
                <i class="fas fa-map-marker-alt text-blue-600 text-2xl"></i>
                <span class="text-2xl font-bold text-gray-900">TravelQuest</span>
            </a>

            <div class="hidden md:flex items-center space-x-8">
                <a href="<?= url('/') ?>" class="text-sm font-medium transition-colors <?= ($currentPage === 'home') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-blue-600' ?>"><?= __('nav.home') ?></a>
                <a href="<?= url('/tours') ?>" class="text-sm font-medium transition-colors <?= ($currentPage === 'tours') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-blue-600' ?>"><?= __('nav.tours') ?></a>
                <a href="<?= url('/about') ?>" class="text-sm font-medium transition-colors <?= ($currentPage === 'about') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-blue-600' ?>"><?= __('nav.about') ?></a>
                <a href="<?= url('/contact') ?>" class="text-sm font-medium transition-colors <?= ($currentPage === 'contact') ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-600 hover:text-blue-600' ?>"><?= __('nav.contact') ?></a>
            </div>

            <div class="hidden md:flex items-center space-x-4">
                <!-- Language Switcher -->
                <div class="relative group">
                    <button class="flex items-center space-x-1 px-3 py-2 text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-globe"></i>
                        <span class="text-sm font-medium"><?= strtoupper($currentLang) ?></span>
                        <i class="fas fa-chevron-down text-xs"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-32 bg-white rounded-lg shadow-lg py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                        <a href="<?= url('/lang/en') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?= $currentLang === 'en' ? 'bg-blue-50 text-blue-600' : '' ?>">
                            <i class="fas fa-check mr-2 <?= $currentLang === 'en' ? '' : 'invisible' ?>"></i>English
                        </a>
                        <a href="<?= url('/lang/vi') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 <?= $currentLang === 'vi' ? 'bg-blue-50 text-blue-600' : '' ?>">
                            <i class="fas fa-check mr-2 <?= $currentLang === 'vi' ? '' : 'invisible' ?>"></i>Tiếng Việt
                        </a>
                    </div>
                </div>

                <?php if ($isLoggedIn): ?>
                    <a href="<?= url('/dashboard?view=wishlist') ?>" class="p-2 text-gray-600 hover:text-blue-600 transition-colors">
                        <i class="fas fa-heart"></i>
                    </a>
                    <a href="<?= url('/dashboard') ?>" class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-user"></i>
                        <span><?= htmlspecialchars($user['full_name'] ?? __('nav.dashboard')) ?></span>
                    </a>
                    <a href="<?= url('/logout') ?>" class="px-4 py-2 text-gray-600 hover:text-gray-800"><?= __('nav.logout') ?></a>
                <?php else: ?>
                    <a href="<?= url('/login') ?>" class="flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-user"></i>
                        <span><?= __('nav.sign_in') ?></span>
                    </a>
                <?php endif; ?>
            </div>

            <button class="md:hidden p-2 text-gray-600" onclick="toggleMobileMenu()">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </div>

    <div id="mobileMenu" class="hidden md:hidden bg-white border-t">
        <div class="px-4 py-3 space-y-3">
            <a href="<?= url('/') ?>" class="block px-3 py-2 text-gray-600 hover:bg-gray-50 rounded-lg"><?= __('nav.home') ?></a>
            <a href="<?= url('/tours') ?>" class="block px-3 py-2 text-gray-600 hover:bg-gray-50 rounded-lg"><?= __('nav.tours') ?></a>
            <a href="<?= url('/about') ?>" class="block px-3 py-2 text-gray-600 hover:bg-gray-50 rounded-lg"><?= __('nav.about') ?></a>
            <a href="<?= url('/contact') ?>" class="block px-3 py-2 text-gray-600 hover:bg-gray-50 rounded-lg"><?= __('nav.contact') ?></a>
            
            <!-- Mobile Language Switcher -->
            <div class="border-t border-gray-200 pt-3 mt-3">
                <div class="flex items-center justify-between px-3 py-2">
                    <span class="text-sm text-gray-600"><?= __('common.language') ?? 'Language' ?>:</span>
                    <div class="flex space-x-2">
                        <a href="<?= url('/lang/en') ?>" class="px-3 py-1 text-sm rounded <?= $currentLang === 'en' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' ?>">EN</a>
                        <a href="<?= url('/lang/vi') ?>" class="px-3 py-1 text-sm rounded <?= $currentLang === 'vi' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' ?>">VI</a>
                    </div>
                </div>
            </div>

            <?php if ($isLoggedIn): ?>
                <a href="<?= url('/dashboard') ?>" class="block px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"><?= __('nav.dashboard') ?></a>
                <a href="<?= url('/logout') ?>" class="block px-3 py-2 text-gray-600 hover:bg-gray-50 rounded-lg"><?= __('nav.logout') ?></a>
            <?php else: ?>
                <a href="<?= url('/login') ?>" class="block px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"><?= __('nav.sign_in') ?></a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
function toggleMobileMenu() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
}
</script>
