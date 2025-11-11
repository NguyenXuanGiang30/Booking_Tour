<?php
require_once __DIR__ . '/../includes/i18n.php';
$pageTitle = __('tours.title') . ' - TravelQuest';
$showNavbar = true;
$showFooter = true;
ob_start();
?>
<div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 mb-2"><?= __('tours.title') ?></h1>
            <p class="text-lg text-gray-600"><?= __('tours.subtitle') ?></p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <div class="lg:w-80 bg-white rounded-xl p-6 shadow-lg h-fit">
                <h2 class="text-xl font-bold text-gray-900 mb-6"><?= __('tours.filters') ?></h2>
                <form method="GET" action="<?= url('/tours') ?>" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= __('tours.location') ?></label>
                        <input type="text" name="location" value="<?= htmlspecialchars($filters['location']) ?>" placeholder="<?= __('tours.location_placeholder') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= __('tours.price_range') ?></label>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="minPrice" value="<?= htmlspecialchars($filters['minPrice']) ?>" placeholder="<?= __('tours.min_price') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <input type="number" name="maxPrice" value="<?= htmlspecialchars($filters['maxPrice']) ?>" placeholder="<?= __('tours.max_price') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= __('tours.max_duration') ?></label>
                        <input type="number" name="duration" value="<?= htmlspecialchars($filters['duration']) ?>" placeholder="<?= __('tours.duration_placeholder') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= __('tours.category') ?></label>
                        <select name="category" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value=""><?= __('tours.all_categories') ?></option>
                            <option value="beach" <?= $filters['category'] === 'beach' ? 'selected' : '' ?>><?= __('category.beach') ?></option>
                            <option value="mountain" <?= $filters['category'] === 'mountain' ? 'selected' : '' ?>><?= __('category.mountain') ?></option>
                            <option value="cultural" <?= $filters['category'] === 'cultural' ? 'selected' : '' ?>><?= __('category.cultural') ?></option>
                            <option value="adventure" <?= $filters['category'] === 'adventure' ? 'selected' : '' ?>><?= __('category.adventure') ?></option>
                            <option value="wildlife" <?= $filters['category'] === 'wildlife' ? 'selected' : '' ?>><?= __('category.wildlife') ?></option>
                            <option value="luxury" <?= $filters['category'] === 'luxury' ? 'selected' : '' ?>><?= __('category.luxury') ?></option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2"><?= __('tours.sort_by') ?></label>
                        <select name="sortBy" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="featured" <?= $filters['sortBy'] === 'featured' ? 'selected' : '' ?>><?= __('tours.sort_featured') ?></option>
                            <option value="price-low" <?= $filters['sortBy'] === 'price-low' ? 'selected' : '' ?>><?= __('tours.sort_price_low') ?></option>
                            <option value="price-high" <?= $filters['sortBy'] === 'price-high' ? 'selected' : '' ?>><?= __('tours.sort_price_high') ?></option>
                            <option value="rating" <?= $filters['sortBy'] === 'rating' ? 'selected' : '' ?>><?= __('tours.sort_rating') ?></option>
                            <option value="duration" <?= $filters['sortBy'] === 'duration' ? 'selected' : '' ?>><?= __('tours.sort_duration') ?></option>
                        </select>
                    </div>

                    <button type="submit" class="w-full py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"><?= __('tours.apply_filters') ?></button>
                    <a href="<?= url('/tours') ?>" class="block w-full py-2 text-center text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50 transition-colors"><?= __('tours.reset_filters') ?></a>
                </form>
            </div>

            <div class="flex-1">
                <p class="text-gray-600 mb-6"><?= __('tours.showing') ?> <span class="font-semibold"><?= count($tours) ?></span> <?= __('tours.tours_count') ?></p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($tours as $tour): ?>
                        <div class="bg-white rounded-xl overflow-hidden shadow-lg hover:shadow-xl transition-shadow cursor-pointer group" onclick="window.location.href='<?= url('/tour/' . $tour['id']) ?>'">
                            <div class="relative h-56 overflow-hidden">
                                <img src="<?= htmlspecialchars(!empty($tour['images']) && is_array($tour['images']) && !empty($tour['images'][0]) ? $tour['images'][0] : 'https://images.pexels.com/photos/346885/pexels-photo-346885.jpeg') ?>" alt="<?= htmlspecialchars($tour['title']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300">
                                <?php if ($tour['featured']): ?>
                                    <div class="absolute top-4 right-4 bg-yellow-400 text-gray-900 px-3 py-1 rounded-full text-sm font-semibold"><?= __('tours.featured') ?></div>
                                <?php endif; ?>
                                <div class="absolute top-4 left-4 bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-semibold capitalize"><?= __('category.' . $tour['category']) ?></div>
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
                                        <span class="text-sm"><?= $tour['duration'] ?><?= __('tours.days_short') ?></span>
                                    </div>
                                    <div class="flex items-center space-x-1 text-gray-600">
                                        <i class="fas fa-users"></i>
                                        <span class="text-sm"><?= __('home.max') ?> <?= $tour['max_guests'] ?></span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500"><?= __('tours.from') ?></div>
                                    <div class="text-xl font-bold text-blue-600">$<?= number_format($tour['price']) ?></div>
                                </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($tours)): ?>
                    <div class="text-center py-12">
                        <p class="text-gray-500 text-lg"><?= __('tours.no_tours_found') ?></p>
                        <a href="<?= url('/tours') ?>" class="mt-4 inline-block text-blue-600 hover:text-blue-700 font-medium"><?= __('tours.reset_filters') ?></a>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <?php
                    require_once __DIR__ . '/../functions/pagination_function.php';
                    $queryParams = array_filter([
                        'location' => $filters['location'] ?? '',
                        'minPrice' => $filters['minPrice'] ?? '',
                        'maxPrice' => $filters['maxPrice'] ?? '',
                        'duration' => $filters['duration'] ?? '',
                        'category' => $filters['category'] ?? '',
                        'sortBy' => $filters['sortBy'] ?? ''
                    ]);
                    $baseUrl = url('/tours');
                    include __DIR__ . '/components/pagination.php';
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
