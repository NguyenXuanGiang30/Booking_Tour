<?php
require_once __DIR__ . '/../includes/i18n.php';
require_once __DIR__ . '/../includes/Auth.php';
$currentLang = i18n::getCurrentLang();
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php if (Auth::isLoggedIn() || Auth::isAdmin()): ?>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <?php endif; ?>
    <title><?= $pageTitle ?? 'TravelQuest - Discover Your Next Adventure' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <?php if ($showNavbar ?? true): ?>
        <?php include __DIR__ . '/components/navbar.php'; ?>
    <?php endif; ?>
    
    <main class="<?= ($showNavbar ?? true) ? 'pt-16 flex-1' : 'flex-1' ?>">
        <?= $content ?? '' ?>
    </main>
    
    <?php if ($showFooter ?? true): ?>
        <?php include __DIR__ . '/components/footer.php'; ?>
    <?php endif; ?>
    
    <script>
        // Simple navigation helper
        function navigate(url) {
            window.location.href = url;
        }
    </script>
</body>
</html>
