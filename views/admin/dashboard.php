<?php
$pageTitle = 'Admin Dashboard - TravelQuest';
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
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?= $pageTitle ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-900 text-white">
            <div class="p-6">
                <div class="flex items-center space-x-2 mb-8">
                    <i class="fas fa-map-marker-alt text-blue-400 text-2xl"></i>
                    <span class="text-xl font-bold">TravelQuest</span>
                </div>
                <div class="mb-6">
                    <p class="text-sm text-gray-400">Welcome back,</p>
                    <p class="font-semibold"><?= htmlspecialchars($admin['full_name'] ?? 'Admin') ?></p>
                </div>
                <nav class="space-y-2">
                    <a href="<?= url('/admin/dashboard') ?>" class="block px-4 py-2 bg-blue-600 rounded-lg">
                        <i class="fas fa-chart-line mr-2"></i> Dashboard
                    </a>
                    <a href="<?= url('/admin/tours') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-map-marked-alt mr-2"></i> Tours
                    </a>
                    <a href="<?= url('/admin/bookings') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-calendar-check mr-2"></i> Bookings
                    </a>
                    <a href="<?= url('/admin/users') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-users mr-2"></i> Users
                    </a>
                    <a href="<?= url('/') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors mt-8">
                        <i class="fas fa-home mr-2"></i> View Website
                    </a>
                    <a href="<?= url('/admin/logout') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-8">Dashboard</h1>

                <!-- Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Tours</p>
                                <p class="text-3xl font-bold text-gray-900"><?= $stats['total_tours'] ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-map-marked-alt text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Users</p>
                                <p class="text-3xl font-bold text-gray-900"><?= $stats['total_users'] ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Bookings</p>
                                <p class="text-3xl font-bold text-gray-900"><?= $stats['total_bookings'] ?></p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-check text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm">Total Revenue</p>
                                <p class="text-3xl font-bold text-gray-900">$<?= number_format($stats['total_revenue'], 2) ?></p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <!-- Bookings by Month -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Bookings by Month</h2>
                        <div style="height: 300px;">
                            <canvas id="bookingsChart"></canvas>
                        </div>
                    </div>

                    <!-- Revenue by Month -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Revenue by Month</h2>
                        <div style="height: 300px;">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Bookings by Status -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Bookings by Status</h2>
                        <div style="height: 300px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>

                    <!-- Additional Stats -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Stats</h2>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Pending Bookings</span>
                                <span class="text-2xl font-bold text-yellow-600"><?= $stats['pending_bookings'] ?></span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Average Booking Value</span>
                                <span class="text-2xl font-bold text-blue-600">
                                    $<?= $stats['paid_bookings'] > 0 ? number_format($stats['total_revenue'] / $stats['paid_bookings'], 2) : '0.00' ?>
                                </span>
                            </div>
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-gray-600">Paid Bookings</span>
                                <span class="text-2xl font-bold text-green-600"><?= $stats['paid_bookings'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Recent Bookings</h2>
                        <a href="<?= url('/admin/bookings') ?>" class="text-blue-600 hover:text-blue-700">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200">
                                    <th class="text-left py-3 px-4 text-gray-600 font-semibold">Tour</th>
                                    <th class="text-left py-3 px-4 text-gray-600 font-semibold">User</th>
                                    <th class="text-left py-3 px-4 text-gray-600 font-semibold">Date</th>
                                    <th class="text-left py-3 px-4 text-gray-600 font-semibold">Guests</th>
                                    <th class="text-left py-3 px-4 text-gray-600 font-semibold">Amount</th>
                                    <th class="text-left py-3 px-4 text-gray-600 font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBookings as $booking): ?>
                                    <tr class="border-b border-gray-100 hover:bg-gray-50">
                                        <td class="py-3 px-4"><?= htmlspecialchars($booking['tour_title']) ?></td>
                                        <td class="py-3 px-4"><?= htmlspecialchars($booking['user_name']) ?></td>
                                        <td class="py-3 px-4"><?= date('M j, Y', strtotime($booking['booking_date'])) ?></td>
                                        <td class="py-3 px-4"><?= $booking['number_of_guests'] ?></td>
                                        <td class="py-3 px-4 font-semibold">$<?= number_format($booking['total_price'], 2) ?></td>
                                        <td class="py-3 px-4">
                                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $booking['status'] === 'paid' ? 'bg-green-100 text-green-800' : ($booking['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Wait for DOM to be ready
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Bookings by Month Chart
                const bookingsCtx = document.getElementById('bookingsChart');
                if (bookingsCtx) {
                    const bookingsLabels = <?= json_encode(array_column($bookingsByMonth ?? [], 'month')) ?>;
                    const bookingsData = <?= json_encode(array_column($bookingsByMonth ?? [], 'count')) ?>;
                    
                    new Chart(bookingsCtx.getContext('2d'), {
                        type: 'line',
                        data: {
                            labels: bookingsLabels.length > 0 ? bookingsLabels : ['No Data'],
                            datasets: [{
                                label: 'Bookings',
                                data: bookingsData.length > 0 ? bookingsData : [0],
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: 2,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
                    });
                }

                // Revenue by Month Chart
                const revenueCtx = document.getElementById('revenueChart');
                if (revenueCtx) {
                    const revenueLabels = <?= json_encode(array_column($revenueByMonth ?? [], 'month')) ?>;
                    const revenueData = <?= json_encode(array_column($revenueByMonth ?? [], 'total')) ?>;
                    
                    new Chart(revenueCtx.getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: revenueLabels.length > 0 ? revenueLabels : ['No Data'],
                            datasets: [{
                                label: 'Revenue ($)',
                                data: revenueData.length > 0 ? revenueData : [0],
                                backgroundColor: 'rgba(34, 197, 94, 0.6)',
                                borderColor: 'rgb(34, 197, 94)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: 2,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + value.toLocaleString();
                                        }
                                    }
                                }
                            }
                        }
                    });
                }

                // Bookings by Status Chart
                const statusCtx = document.getElementById('statusChart');
                if (statusCtx) {
                    const statusLabels = <?= json_encode(array_column($bookingsByStatus ?? [], 'status')) ?>;
                    const statusData = <?= json_encode(array_column($bookingsByStatus ?? [], 'count')) ?>;
                    
                    // Default colors for different statuses
                    const statusColors = {
                        'pending': { bg: 'rgba(234, 179, 8, 0.6)', border: 'rgb(234, 179, 8)' },
                        'paid': { bg: 'rgba(34, 197, 94, 0.6)', border: 'rgb(34, 197, 94)' },
                        'completed': { bg: 'rgba(107, 114, 128, 0.6)', border: 'rgb(107, 114, 128)' },
                        'canceled': { bg: 'rgba(239, 68, 68, 0.6)', border: 'rgb(239, 68, 68)' }
                    };
                    
                    const hasData = statusLabels.length > 0 && statusData.length > 0;
                    
                    new Chart(statusCtx.getContext('2d'), {
                        type: 'doughnut',
                        data: {
                            labels: hasData ? statusLabels : ['No Data'],
                            datasets: [{
                                data: hasData ? statusData : [1],
                                backgroundColor: hasData ? statusLabels.map(status => statusColors[status]?.bg || 'rgba(156, 163, 175, 0.6)') : ['rgba(156, 163, 175, 0.6)'],
                                borderColor: hasData ? statusLabels.map(status => statusColors[status]?.border || 'rgb(156, 163, 175)') : ['rgb(156, 163, 175)'],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: 2,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error initializing charts:', error);
            }
        });
    </script>
</body>
</html>
<?php
$content = ob_get_clean();
echo $content;
?>
