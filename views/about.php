<?php
$pageTitle = __('about.title');
$showNavbar = true;
$showFooter = true;
ob_start();
?>
<div class="min-h-screen bg-gray-50">
    <section class="relative h-96 bg-cover bg-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url(https://images.pexels.com/photos/2662116/pexels-photo-2662116.jpeg);">
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center text-white px-4">
                <h1 class="text-5xl font-bold mb-4"><?= __('about.page_title') ?></h1>
                <p class="text-xl"><?= __('about.subtitle') ?></p>
            </div>
        </div>
    </section>

    <section class="py-16 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg p-8 mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6"><?= __('about.our_story') ?></h2>
                <div class="space-y-4 text-gray-600 leading-relaxed">
                    <p><?= __('about.story_para1') ?></p>
                    <p><?= __('about.story_para2') ?></p>
                    <p><?= __('about.story_para3') ?></p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-900 mb-2">50,000+</h3>
                    <p class="text-gray-600"><?= __('about.happy_travelers') ?></p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-globe text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-900 mb-2">120+</h3>
                    <p class="text-gray-600"><?= __('about.destinations') ?></p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-award text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-900 mb-2">15+</h3>
                    <p class="text-gray-600"><?= __('about.industry_awards') ?></p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center"><?= __('about.why_choose_us') ?></h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shield-alt text-blue-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2"><?= __('about.safe_secure') ?></h3>
                            <p class="text-gray-600"><?= __('about.safe_secure_desc') ?></p>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-award text-blue-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2"><?= __('about.expert_guides') ?></h3>
                            <p class="text-gray-600"><?= __('about.expert_guides_desc') ?></p>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-heart text-blue-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2"><?= __('about.personalized_service') ?></h3>
                            <p class="text-gray-600"><?= __('about.personalized_service_desc') ?></p>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-blue-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2"><?= __('about.best_value') ?></h3>
                            <p class="text-gray-600"><?= __('about.best_value_desc') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 px-4 bg-blue-600 text-white">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl font-bold mb-6"><?= __('about.ready_adventure') ?></h2>
            <p class="text-xl mb-8"><?= __('about.ready_subtitle') ?></p>
            <a href="<?= url('/tours') ?>" class="bg-white text-blue-600 px-8 py-4 rounded-lg hover:bg-gray-100 transition-colors font-semibold text-lg"><?= __('about.explore_tours') ?></a>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
