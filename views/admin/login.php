<?php
$pageTitle = 'Admin Login - TravelQuest';
$showNavbar = false;
$showFooter = false;
$error = $error ?? '';
ob_start();
?>
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <div class="flex items-center justify-center space-x-2 mb-4">
                <i class="fas fa-map-marker-alt text-blue-600 text-4xl"></i>
                <span class="text-3xl font-bold text-gray-900">TravelQuest</span>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Admin Login</h2>
            <p class="text-gray-600">Sign in to access admin panel</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-8">
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= url('/admin/login') ?>" class="space-y-6">
                <?php require_once __DIR__ . '/../../includes/Auth.php'; ?>
                <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username or Email</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="username" required placeholder="admin" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" required placeholder="Enter your password" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">Sign In</button>
            </form>

            <div class="mt-6 text-center">
                <a href="<?= url('/') ?>" class="text-sm text-gray-600 hover:text-blue-600">Back to website</a>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
