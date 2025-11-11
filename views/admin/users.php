<?php
$pageTitle = 'Manage Users - Admin';
$showNavbar = false;
$showFooter = false;
$admin = Auth::getAdmin();
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
                    <a href="<?= url('/admin/tours') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-map-marked-alt mr-2"></i> Tours
                    </a>
                    <a href="<?= url('/admin/bookings') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-calendar-check mr-2"></i> Bookings
                    </a>
                    <a href="<?= url('/admin/users') ?>" class="block px-4 py-2 bg-blue-600 rounded-lg">
                        <i class="fas fa-users mr-2"></i> Users
                    </a>
                    <a href="<?= url('/admin/logout') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors mt-8">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </nav>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Users</h1>

            <!-- Search Form -->
            <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
                <form method="GET" action="<?= url('/admin/users') ?>" class="flex items-center space-x-4">
                    <div class="flex-1">
                        <input type="text" 
                               name="search" 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                               placeholder="Search by name, email, or phone..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                    <?php if (!empty($_GET['search'])): ?>
                        <a href="<?= url('/admin/users') ?>" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors font-semibold">
                            <i class="fas fa-times mr-2"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (empty($users)): ?>
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <p class="text-gray-600">No users found<?= !empty($_GET['search']) ? ' matching your search.' : '.' ?></p>
                </div>
            <?php else: ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <p class="text-gray-600">Showing <span class="font-semibold"><?= count($users) ?></span> user(s)</p>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-4 px-6 text-gray-600 font-semibold">Name</th>
                            <th class="text-left py-4 px-6 text-gray-600 font-semibold">Email</th>
                            <th class="text-left py-4 px-6 text-gray-600 font-semibold">Phone</th>
                            <th class="text-left py-4 px-6 text-gray-600 font-semibold">Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-4 px-6 font-semibold"><?= htmlspecialchars($user['full_name'] ?: 'N/A') ?></td>
                                <td class="py-4 px-6"><?= htmlspecialchars($user['email']) ?></td>
                                <td class="py-4 px-6"><?= htmlspecialchars($user['phone'] ?: 'N/A') ?></td>
                                <td class="py-4 px-6"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <?php
                    require_once __DIR__ . '/../../functions/pagination_function.php';
                    $queryParams = array_filter(['search' => $_GET['search'] ?? '']);
                    $baseUrl = url('/admin/users');
                    include __DIR__ . '/../components/pagination.php';
                    ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
<?php
$content = ob_get_clean();
echo $content;
?>
