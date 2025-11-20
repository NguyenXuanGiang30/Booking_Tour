<?php
$pageTitle = (isset($tour) ? 'Edit' : 'Create') . ' Tour - Admin';
$showNavbar = false;
$showFooter = false;
$admin = Auth::getAdmin();
$isEdit = isset($tour);
$tour = $tour ?? [
    'title' => '',
    'description' => '',
    'location' => '',
    'price' => '',
    'duration' => 1,
    'max_guests' => 10,
    'images' => [],
    'itinerary' => [],
    'included' => [],
    'excluded' => [],
    'featured' => false,
    'category' => 'adventure'
];
$error = $error ?? '';
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <div class="w-64 bg-gray-900 text-white">
            <div class="p-6">
                <div class="flex items-center space-x-2 mb-8">
                    <i class="fas fa-map-marker-alt text-blue-400 text-2xl"></i>
                    <span class="text-xl font-bold">TravelQuest</span>
                </div>
                <nav class="space-y-2">
                    <a href="<?= url('/admin/dashboard') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-chart-line mr-2"></i> Dashboard
                    </a>
                    <a href="<?= url('/admin/tours') ?>" class="block px-4 py-2 bg-blue-600 rounded-lg">
                        <i class="fas fa-map-marked-alt mr-2"></i> Tours
                    </a>
                    <a href="<?= url('/admin/bookings') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-calendar-check mr-2"></i> Bookings
                    </a>
                    <a href="<?= url('/admin/users') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-users mr-2"></i> Users
                    </a>
                    <a href="<?= url('/admin/logout') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors mt-8">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </nav>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-900 mb-6"><?= $isEdit ? 'Edit Tour' : 'Create New Tour' ?></h1>

                <?php if ($error): ?>
                    <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="<?= url('/admin/tours/' . ($isEdit ? 'edit/' . $tour['id'] : 'create')) ?>" class="bg-white rounded-xl shadow-lg p-8 space-y-6">
                    <?php require_once __DIR__ . '/../../includes/Auth.php'; ?>
                    <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($tour['title']) ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location *</label>
                            <input type="text" name="location" value="<?= htmlspecialchars($tour['location']) ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Price *</label>
                            <input type="number" step="0.01" name="price" value="<?= $tour['price'] ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Duration (days) *</label>
                            <input type="number" name="duration" value="<?= $tour['duration'] ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Guests *</label>
                            <input type="number" name="max_guests" value="<?= $tour['max_guests'] ?>" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Category *</label>
                            <select name="category" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="adventure" <?= $tour['category'] === 'adventure' ? 'selected' : '' ?>>Adventure</option>
                                <option value="beach" <?= $tour['category'] === 'beach' ? 'selected' : '' ?>>Beach</option>
                                <option value="mountain" <?= $tour['category'] === 'mountain' ? 'selected' : '' ?>>Mountain</option>
                                <option value="cultural" <?= $tour['category'] === 'cultural' ? 'selected' : '' ?>>Cultural</option>
                                <option value="wildlife" <?= $tour['category'] === 'wildlife' ? 'selected' : '' ?>>Wildlife</option>
                                <option value="luxury" <?= $tour['category'] === 'luxury' ? 'selected' : '' ?>>Luxury</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                        <textarea name="description" rows="5" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($tour['description']) ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Images (comma-separated URLs)</label>
                        <input type="text" name="images" value="<?= htmlspecialchars(implode(',', $tour['images'] ?? [])) ?>" placeholder="https://example.com/image1.jpg, https://example.com/image2.jpg" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Included (one per line)</label>
                        <textarea name="included" rows="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars(implode("\n", $tour['included'] ?? [])) ?></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Excluded (one per line)</label>
                        <textarea name="excluded" rows="5" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars(implode("\n", $tour['excluded'] ?? [])) ?></textarea>
                    </div>

                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="featured" <?= $tour['featured'] ? 'checked' : '' ?> class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Featured Tour</span>
                        </label>
                    </div>

                    <div class="flex items-center space-x-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                            <?= $isEdit ? 'Update Tour' : 'Create Tour' ?>
                        </button>
                        <a href="<?= url('/admin/tours') ?>" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors font-semibold">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<?php
$content = ob_get_clean();
echo $content;
?>
