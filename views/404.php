<?php
$pageTitle = '404 - Page Not Found';
$showNavbar = true;
$showFooter = true;
ob_start();
?>
<div class="min-h-screen bg-gray-50 flex items-center justify-center px-4">
    <div class="text-center">
        <h1 class="text-9xl font-bold text-gray-300">404</h1>
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Page Not Found</h2>
        <p class="text-gray-600 mb-8">The page you're looking for doesn't exist.</p>
        <a href="<?= url('/') ?>" class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">Go Home</a>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
