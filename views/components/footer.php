<footer class="bg-gray-900 text-white py-12 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <i class="fas fa-map-marker-alt text-blue-400 text-xl"></i>
                    <span class="text-xl font-bold">TravelQuest</span>
                </div>
<?php
require_once __DIR__ . '/../../includes/i18n.php';
?>
                <p class="text-gray-400"><?= __('footer.description') ?></p>
            </div>
            
            <div>
                <h3 class="font-semibold mb-4"><?= __('footer.quick_links') ?></h3>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="<?= url('/') ?>" class="hover:text-white transition-colors"><?= __('nav.home') ?></a></li>
                    <li><a href="<?= url('/tours') ?>" class="hover:text-white transition-colors"><?= __('nav.tours') ?></a></li>
                    <li><a href="<?= url('/about') ?>" class="hover:text-white transition-colors"><?= __('nav.about') ?></a></li>
                    <li><a href="<?= url('/contact') ?>" class="hover:text-white transition-colors"><?= __('nav.contact') ?></a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="font-semibold mb-4"><?= __('footer.support') ?></h3>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="#" class="hover:text-white transition-colors">FAQ</a></li>
                    <li><a href="#" class="hover:text-white transition-colors"><?= __('auth.terms') ?></a></li>
                    <li><a href="#" class="hover:text-white transition-colors"><?= __('auth.privacy') ?></a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Cancellation Policy</a></li>
                </ul>
            </div>
            
            <div>
                <h3 class="font-semibold mb-4"><?= __('footer.contact_us') ?></h3>
                <ul class="space-y-2 text-gray-400">
                    <li><i class="fas fa-envelope mr-2"></i> info@travelquest.com</li>
                    <li><i class="fas fa-phone mr-2"></i> +1 (555) 123-4567</li>
                    <li><i class="fas fa-map-marker-alt mr-2"></i> 123 Travel St, Adventure City</li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
            <p>&copy; <?= date('Y') ?> TravelQuest. <?= __('footer.all_rights') ?></p>
        </div>
    </div>
</footer>
