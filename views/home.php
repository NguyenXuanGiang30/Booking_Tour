<?php
require_once __DIR__ . '/../includes/i18n.php';
$pageTitle = 'TravelQuest - ' . __('home.title');
$showNavbar = true;
$showFooter = true;
ob_start();
?>
<div class="min-h-screen">
    <section class="relative h-[600px] bg-cover bg-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url(https://images.pexels.com/photos/346885/pexels-photo-346885.jpeg);">
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center text-white px-4 max-w-4xl">
                <h1 class="text-5xl md:text-6xl font-bold mb-6"><?= __('home.title') ?></h1>
                <p class="text-xl md:text-2xl mb-12 text-gray-100"><?= __('home.subtitle') ?></p>

                <div class="bg-white rounded-lg shadow-2xl p-6 max-w-4xl mx-auto">
                    <form action="<?= url('/tours') ?>" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2 text-left"><?= __('home.destination') ?></label>
                            <div class="relative">
                                <i class="fas fa-map-marker-alt absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="text" name="location" placeholder="<?= __('home.where_to') ?>" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900">
                            </div>
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2 text-left"><?= __('home.date') ?></label>
                            <div class="relative">
                                <i class="fas fa-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="date" name="date" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900">
                            </div>
                        </div>

                        <div class="md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 mb-2 text-left"><?= __('home.guests') ?></label>
                            <div class="relative">
                                <i class="fas fa-users absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <select name="guests" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-gray-900 appearance-none">
                                    <?php for ($i = 1; $i <= 8; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?> <?= $i === 1 ? __('home.guest') : __('home.guests_plural') ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="md:col-span-1 flex items-end">
                            <button type="submit" class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center justify-center space-x-2">
                                <i class="fas fa-search"></i>
                                <span><?= __('home.search') ?></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 px-4 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4"><?= __('home.featured_tours') ?></h2>
                <p class="text-lg text-gray-600"><?= __('home.featured_tours_subtitle') ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($featuredTours as $tour): ?>
                    <div class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow cursor-pointer group" onclick="window.location.href='<?= url('/tour/' . $tour['id']) ?>'">
                        <div class="relative h-64 overflow-hidden">
                            <img src="<?= htmlspecialchars(!empty($tour['images']) && is_array($tour['images']) && !empty($tour['images'][0]) ? $tour['images'][0] : 'https://images.pexels.com/photos/346885/pexels-photo-346885.jpeg') ?>" alt="<?= htmlspecialchars($tour['title']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                            <?php if ($tour['featured']): ?>
                                <div class="absolute top-4 right-4 bg-yellow-400 text-gray-900 px-3 py-1 rounded-full text-sm font-semibold"><?= __('home.featured') ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="p-6">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-map-marker-alt text-gray-500"></i>
                                    <span class="text-sm text-gray-600"><?= htmlspecialchars($tour['location']) ?></span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <i class="fas fa-star text-yellow-400"></i>
                                    <span class="text-sm font-medium"><?= number_format($tour['rating'], 1) ?></span>
                                    <span class="text-sm text-gray-500">(<?= $tour['total_reviews'] ?>)</span>
                                </div>
                            </div>

                            <h3 class="text-xl font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($tour['title']) ?></h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?= htmlspecialchars($tour['description']) ?></p>

                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <div class="flex items-center space-x-4">
                                    <div class="flex items-center space-x-1 text-gray-600">
                                        <i class="far fa-clock"></i>
                                        <span class="text-sm"><?= $tour['duration'] ?> <?= __('home.days') ?></span>
                                    </div>
                                    <div class="flex items-center space-x-1 text-gray-600">
                                        <i class="fas fa-users"></i>
                                        <span class="text-sm"><?= __('home.max') ?> <?= $tour['max_guests'] ?></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500"><?= __('home.from') ?></div>
                                    <div class="text-2xl font-bold text-blue-600">$<?= number_format($tour['price']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-12">
                <a href="<?= url('/tours') ?>" class="inline-flex items-center space-x-2 bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    <span><?= __('home.view_all_tours') ?></span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <section class="py-16 px-4 bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4"><?= __('home.popular_destinations') ?></h2>
                <p class="text-lg text-gray-600"><?= __('home.popular_destinations_subtitle') ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php
                $destinations = [
                    ['name' => 'Bali, Indonesia', 'image' => 'https://images.pexels.com/photos/2166553/pexels-photo-2166553.jpeg', 'tours' => 12],
                    ['name' => 'Tokyo, Japan', 'image' => 'https://images.pexels.com/photos/161251/senso-ji-temple-japan-kyoto-landmark-161251.jpeg', 'tours' => 8],
                    ['name' => 'Santorini, Greece', 'image' => 'https://images.pexels.com/photos/1010657/pexels-photo-1010657.jpeg', 'tours' => 6],
                    ['name' => 'Swiss Alps', 'image' => 'https://images.pexels.com/photos/1660995/pexels-photo-1660995.jpeg', 'tours' => 5],
                ];
                foreach ($destinations as $destination):
                ?>
                    <div class="relative h-80 rounded-xl overflow-hidden group cursor-pointer" onclick="window.location.href='<?= url('/tours') ?>'">
                        <img src="<?= $destination['image'] ?>" alt="<?= $destination['name'] ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent flex flex-col justify-end p-6">
                            <h3 class="text-2xl font-bold text-white mb-1"><?= $destination['name'] ?></h3>
                            <p class="text-gray-200"><?= $destination['tours'] ?> <?= __('home.tours_available') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-16 px-4 bg-blue-600">
        <div class="max-w-7xl mx-auto text-center text-white">
            <h2 class="text-4xl font-bold mb-4"><?= __('home.special_offer') ?></h2>
            <p class="text-xl mb-8"><?= __('home.special_offer_text') ?></p>
            <a href="<?= url('/tours') ?>" class="bg-white text-blue-600 px-8 py-3 rounded-lg hover:bg-gray-100 transition-colors font-medium"><?= __('home.explore_deals') ?></a>
        </div>
    </section>

    <section class="py-16 px-4 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4"><?= __('home.testimonials') ?></h2>
                <p class="text-lg text-gray-600"><?= __('home.testimonials_subtitle') ?></p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php
                $reviews = [
                    ['name' => 'Sarah Johnson', 'avatar' => 'https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg', 'rating' => 5, 'comment' => 'The Bali Paradise Adventure was absolutely incredible! Every detail was perfectly organized, and our guide was knowledgeable and friendly. Highly recommend!', 'tour' => 'Bali Paradise Adventure'],
                    ['name' => 'Michael Chen', 'avatar' => 'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg', 'rating' => 5, 'comment' => 'Amazing experience in the Swiss Alps! The views were breathtaking, and the accommodations exceeded our expectations. Worth every penny!', 'tour' => 'Swiss Alps Mountain Escape'],
                    ['name' => 'Emma Rodriguez', 'avatar' => 'https://images.pexels.com/photos/415829/pexels-photo-415829.jpeg', 'rating' => 5, 'comment' => 'Tokyo was a dream come true! The cultural experiences and city tours were perfectly balanced. Our guide made everything so easy and enjoyable.', 'tour' => 'Tokyo Cultural Experience'],
                ];
                foreach ($reviews as $review):
                ?>
                    <div class="bg-white rounded-xl p-6 shadow-lg">
                        <div class="flex items-center space-x-4 mb-4">
                            <img src="<?= $review['avatar'] ?>" alt="<?= $review['name'] ?>" class="w-14 h-14 rounded-full object-cover">
                            <div>
                                <h4 class="font-semibold text-gray-900"><?= $review['name'] ?></h4>
                                <div class="flex items-center space-x-1">
                                    <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                        <i class="fas fa-star text-yellow-400"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 mb-3"><?= $review['comment'] ?></p>
                        <p class="text-sm text-blue-600 font-medium"><?= $review['tour'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
