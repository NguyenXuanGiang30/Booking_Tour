<?php
require_once __DIR__ . '/../includes/i18n.php';
$pageTitle = __('nav.sign_in') . ' - TravelQuest';
$showNavbar = false;
$showFooter = false;
$error = $error ?? '';
ob_start();
?>
<div class="min-h-screen flex items-center justify-center py-12 px-4 relative overflow-hidden" style="background-image: url('https://images.unsplash.com/photo-1488646953014-85cb44e25828?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80'); background-size: cover; background-position: center; background-repeat: no-repeat;">
    <!-- Overlay for better readability -->
    <div class="absolute inset-0 bg-black/40"></div>
    
    <div class="max-w-md w-full relative z-10">
        <div class="text-center mb-8">
            <div class="flex items-center justify-center space-x-2 mb-4">
                <i class="fas fa-map-marker-alt text-white text-4xl drop-shadow-lg"></i>
                <span class="text-3xl font-bold text-white drop-shadow-lg">TravelQuest</span>
            </div>
            <h2 class="text-2xl font-bold text-white mb-2 drop-shadow-lg"><?= __('auth.welcome_back') ?></h2>
            <p class="text-white/90 drop-shadow-md"><?= __('auth.sign_in_subtitle') ?></p>
        </div>

        <div class="bg-white/10 backdrop-blur-md rounded-xl shadow-2xl p-8 border border-white/20">
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-500/80 backdrop-blur-sm border border-red-400/50 text-white rounded-lg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= url('/login') ?>" class="space-y-6">
                <?php require_once __DIR__ . '/../includes/Auth.php'; ?>
                <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
                <div>
                    <label class="block text-sm font-medium text-white mb-2 drop-shadow-md"><?= __('auth.email') ?> / Username</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-3 top-1/2 transform -translate-y-1/2 text-white/70"></i>
                        <input type="text" name="email" required placeholder="Email or Username" class="w-full pl-10 pr-4 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/60 focus:ring-2 focus:ring-white/50 focus:border-white/50 focus:bg-white/30 transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-white mb-2 drop-shadow-md"><?= __('auth.password') ?></label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 transform -translate-y-1/2 text-white/70"></i>
                        <input type="password" name="password" required placeholder="<?= __('auth.password_placeholder') ?>" class="w-full pl-10 pr-4 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg text-white placeholder-white/60 focus:ring-2 focus:ring-white/50 focus:border-white/50 focus:bg-white/30 transition-all">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" class="w-4 h-4 text-blue-600 border-white/30 rounded focus:ring-white/50 bg-white/20">
                        <span class="ml-2 text-sm text-white drop-shadow-md"><?= __('auth.remember_me') ?></span>
                    </label>
                    <a href="#" class="text-sm text-white/90 hover:text-white drop-shadow-md transition-colors"><?= __('auth.forgot_password') ?></a>
                </div>

                <button type="submit" class="w-full bg-blue-600/90 backdrop-blur-sm text-white py-3 rounded-lg hover:bg-blue-700/90 transition-all font-semibold shadow-lg border border-white/20"><?= __('auth.sign_in') ?></button>
            </form>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-white/30"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white/10 backdrop-blur-sm text-white/80"><?= __('auth.or_continue_with') ?></span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <button class="flex items-center justify-center px-4 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg hover:bg-white/30 transition-all text-white">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        <span class="ml-2 text-sm font-medium">Google</span>
                    </button>
                    <button class="flex items-center justify-center px-4 py-3 bg-white/20 backdrop-blur-sm border border-white/30 rounded-lg hover:bg-white/30 transition-all text-white">
                        <svg class="w-5 h-5" fill="#1877F2" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                        <span class="ml-2 text-sm font-medium">Facebook</span>
                    </button>
                </div>
            </div>

            <p class="mt-6 text-center text-sm text-white/90 drop-shadow-md">
                <?= __('auth.dont_have_account') ?> <a href="<?= url('/register') ?>" class="text-white font-medium hover:text-white/80 transition-colors underline"><?= __('auth.sign_up') ?></a>
            </p>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
