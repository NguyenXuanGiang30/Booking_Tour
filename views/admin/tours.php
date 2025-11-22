<?php
$pageTitle = 'Manage Tours - Admin';
$showNavbar = false;
$showFooter = false;
$admin = Auth::getAdmin();
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
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
                    <a href="<?= url('/admin/coupons') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-ticket-alt mr-2"></i> Coupons
                    </a>
                    <a href="<?= url('/admin/logout') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors mt-8">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </nav>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Manage Tours</h1>
                <a href="<?= url('/admin/tours/create') ?>" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                    <i class="fas fa-plus mr-2"></i> Add New Tour
                </a>
            </div>

            <?php if ($success): ?>
                <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <!-- Search Form -->
            <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
                <form method="GET" action="<?= url('/admin/tours') ?>" class="flex items-center space-x-4">
                    <div class="flex-1">
                        <input type="text" 
                               name="search" 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                               placeholder="Search by title, location, or description..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                    <?php if (!empty($_GET['search'])): ?>
                        <a href="<?= url('/admin/tours') ?>" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors font-semibold">
                            <i class="fas fa-times mr-2"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (empty($tours)): ?>
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <p class="text-gray-600">No tours found<?= !empty($_GET['search']) ? ' matching your search.' : '.' ?></p>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="p-4 bg-gray-50 border-b border-gray-200">
                        <p class="text-gray-600">Showing <span class="font-semibold"><?= count($tours) ?></span> tour(s)</p>
                    </div>
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left py-4 px-6 text-gray-600 font-semibold">Title</th>
                                <th class="text-left py-4 px-6 text-gray-600 font-semibold">Location</th>
                                <th class="text-left py-4 px-6 text-gray-600 font-semibold">Price</th>
                                <th class="text-left py-4 px-6 text-gray-600 font-semibold">Duration</th>
                                <th class="text-left py-4 px-6 text-gray-600 font-semibold">Rating</th>
                                <th class="text-left py-4 px-6 text-gray-600 font-semibold">Featured</th>
                                <th class="text-left py-4 px-6 text-gray-600 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tours as $tour): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50">
                                    <td class="py-4 px-6"><?= htmlspecialchars($tour['title']) ?></td>
                                    <td class="py-4 px-6"><?= htmlspecialchars($tour['location']) ?></td>
                                    <td class="py-4 px-6 font-semibold">$<?= number_format($tour['price'], 2) ?></td>
                                    <td class="py-4 px-6"><?= $tour['duration'] ?> days</td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-1">
                                            <i class="fas fa-star text-yellow-400"></i>
                                            <span><?= number_format($tour['rating'] ?? 0, 1) ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <?php if ($tour['featured']): ?>
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">Yes</span>
                                        <?php else: ?>
                                            <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm">No</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center space-x-2">
                                            <a href="<?= url('/admin/tours/edit/' . $tour['id']) ?>" class="text-blue-600 hover:text-blue-700" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= url('/admin/tours/delete/' . $tour['id']) ?>" onclick="return confirm('Are you sure you want to delete this tour?')" class="text-red-600 hover:text-red-700" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <?php
                    require_once __DIR__ . '/../../functions/pagination_function.php';
                    $queryParams = array_filter(['search' => $_GET['search'] ?? '']);
                    $baseUrl = url('/admin/tours');
                    include __DIR__ . '/../components/pagination.php';
                    ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
$content = ob_get_clean();
echo $content;
?>
