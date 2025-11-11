<?php
$pageTitle = 'Manage Bookings - Admin';
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
                    <a href="<?= url('/admin/bookings') ?>" class="block px-4 py-2 bg-blue-600 rounded-lg">
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
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Manage Bookings</h1>

            <!-- Search and Filter Form -->
            <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
                <form method="GET" action="<?= url('/admin/bookings') ?>" class="flex items-center space-x-4">
                    <div class="flex-1">
                        <input type="text" 
                               name="search" 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                               placeholder="Search by tour title, user name, or email..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= ($_GET['status'] ?? '') === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="completed" <?= ($_GET['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                            <option value="canceled" <?= ($_GET['status'] ?? '') === 'canceled' ? 'selected' : '' ?>>Canceled</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                    <?php if (!empty($_GET['search']) || !empty($_GET['status'])): ?>
                        <a href="<?= url('/admin/bookings') ?>" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors font-semibold">
                            <i class="fas fa-times mr-2"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (empty($bookings)): ?>
                <div class="bg-white rounded-xl shadow-lg p-8 text-center">
                    <p class="text-gray-600">No bookings found<?= !empty($_GET['search']) || !empty($_GET['status']) ? ' matching your search.' : '.' ?></p>
                </div>
            <?php else: ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <p class="text-gray-600">Showing <span class="font-semibold"><?= count($bookings) ?></span> booking(s)</p>
                </div>
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-4 px-6 text-gray-600 font-semibold">Tour</th>
                            <th class="text-left py-4 px-6 text-gray-600 font-semibold">User</th>
                            <th class="text-left py-4 px-6 text-gray-600 font-semibold">Booking Date</th>
                            <th class="text-left py-4 px-6 text-gray-600 font-semibold">Guests</th>
                            <th class="text-left py-4 px-6 text-gray-600 font-semibold">Amount</th>
                            <th class="text-left py-4 px-6 text-gray-600 font-semibold">Status</th>
                            <th class="text-left py-4 px-6 text-gray-600 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-4 px-6"><?= htmlspecialchars($booking['tour_title']) ?></td>
                                <td class="py-4 px-6">
                                    <div>
                                        <p class="font-semibold"><?= htmlspecialchars($booking['user_name']) ?></p>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($booking['user_email']) ?></p>
                                    </div>
                                </td>
                                <td class="py-4 px-6"><?= date('M j, Y', strtotime($booking['booking_date'])) ?></td>
                                <td class="py-4 px-6"><?= $booking['number_of_guests'] ?></td>
                                <td class="py-4 px-6 font-semibold">$<?= number_format($booking['total_price'], 2) ?></td>
                                <td class="py-4 px-6">
                                    <select onchange="updateStatus('<?= $booking['id'] ?>', this.value)" class="px-3 py-1 rounded-full text-sm font-semibold border <?= $booking['status'] === 'paid' ? 'bg-green-100 text-green-800 border-green-300' : ($booking['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800 border-yellow-300' : 'bg-gray-100 text-gray-800 border-gray-300') ?>">
                                        <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="paid" <?= $booking['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                                        <option value="completed" <?= $booking['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="canceled" <?= $booking['status'] === 'canceled' ? 'selected' : '' ?>>Canceled</option>
                                    </select>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-sm text-gray-500"><?= date('M j, Y', strtotime($booking['created_at'])) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <?php
                    require_once __DIR__ . '/../../functions/pagination_function.php';
                    $queryParams = array_filter([
                        'search' => $_GET['search'] ?? '',
                        'status' => $_GET['status'] ?? ''
                    ]);
                    $baseUrl = url('/admin/bookings');
                    include __DIR__ . '/../components/pagination.php';
                    ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function updateStatus(bookingId, status) {
        const formData = new FormData();
        formData.append('id', bookingId);
        formData.append('status', status);

        fetch('<?= url('/admin/bookings/status') ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Failed to update status');
            }
        });
    }
    </script>
</body>
</html>
<?php
$content = ob_get_clean();
echo $content;
?>
