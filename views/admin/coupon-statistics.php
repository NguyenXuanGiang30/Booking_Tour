<?php
$pageTitle = 'Coupon Statistics - Admin';
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <a href="<?= url('/admin/users') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-users mr-2"></i> Users
                    </a>
                    <a href="<?= url('/admin/coupons') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-ticket-alt mr-2"></i> Coupons
                    </a>
                    <a href="<?= url('/admin/coupons/statistics') ?>" class="block px-4 py-2 bg-blue-600 rounded-lg">
                        <i class="fas fa-chart-bar mr-2"></i> Coupon Statistics
                    </a>
                    <a href="<?= url('/admin/logout') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors mt-8">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </nav>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Coupon Statistics</h1>
                <a href="<?= url('/admin/coupons') ?>" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                    <i class="fas fa-arrow-left mr-2"></i> Back to Coupons
                </a>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Coupons</p>
                            <p class="text-3xl font-bold text-gray-900"><?= $stats['total_coupons'] ?></p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-ticket-alt text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Active Coupons</p>
                            <p class="text-3xl font-bold text-gray-900"><?= $stats['active_coupons'] ?></p>
                        </div>
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Usage</p>
                            <p class="text-3xl font-bold text-gray-900"><?= $stats['total_usage'] ?></p>
                        </div>
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-yellow-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Discount</p>
                            <p class="text-3xl font-bold text-gray-900">$<?= number_format($stats['total_discount'], 2) ?></p>
                        </div>
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-dollar-sign text-purple-600 text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Coupon Usage Frequency -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Coupon Usage Frequency (Last 30 Days)</h2>
                    <div style="height: 300px;">
                        <canvas id="usageFrequencyChart"></canvas>
                    </div>
                </div>

                <!-- Coupon Status Distribution -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Coupon Status Distribution</h2>
                    <div style="height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Coupons Table -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Top Coupons by Usage</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Rank</th>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Code</th>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Discount</th>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Usage Count</th>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Total Discount Given</th>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Status</th>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($stats['top_coupons'])): ?>
                                <tr>
                                    <td colspan="7" class="py-8 px-6 text-center text-gray-500">
                                        No coupons found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($stats['top_coupons'] as $index => $coupon): 
                                    $now = date('Y-m-d H:i:s');
                                    $isExpired = $coupon['valid_to'] < $now;
                                    $isActive = $coupon['status'] === 'active' && !$isExpired;
                                    
                                    $discountText = $coupon['discount_type'] === 'percentage' 
                                        ? $coupon['discount_value'] . '%' 
                                        : '$' . number_format($coupon['discount_value'], 2);
                                    if ($coupon['discount_type'] === 'percentage' && $coupon['max_discount']) {
                                        $discountText .= ' (max $' . number_format($coupon['max_discount'], 2) . ')';
                                    }
                                    
                                    $totalDiscountGiven = floatval($coupon['total_discount_given'] ?? 0);
                                ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-4 px-6">
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                                #<?= $index + 1 ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="font-semibold text-gray-900"><?= htmlspecialchars($coupon['code']) ?></div>
                                        </td>
                                        <td class="py-4 px-6 font-semibold text-green-600"><?= $discountText ?></td>
                                        <td class="py-4 px-6">
                                            <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-semibold">
                                                <?= $coupon['used_count'] ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 font-semibold text-purple-600">
                                            $<?= number_format($totalDiscountGiven, 2) ?>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $isActive ? 'bg-green-100 text-green-800' : ($isExpired ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') ?>">
                                                <?= $isExpired ? 'Expired' : ucfirst($coupon['status']) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <a href="<?= url('/admin/coupons/edit/' . $coupon['id']) ?>" class="text-blue-600 hover:text-blue-800" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Additional Stats -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Stats</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Expired Coupons</span>
                            <span class="text-2xl font-bold text-red-600"><?= $stats['expired_coupons'] ?></span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Average Usage per Coupon</span>
                            <span class="text-2xl font-bold text-blue-600">
                                <?= $stats['total_coupons'] > 0 ? number_format($stats['total_usage'] / $stats['total_coupons'], 2) : '0.00' ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-gray-600">Average Discount per Usage</span>
                            <span class="text-2xl font-bold text-green-600">
                                $<?= $stats['total_usage'] > 0 ? number_format($stats['total_discount'] / $stats['total_usage'], 2) : '0.00' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Export Data</h2>
                    <div class="space-y-4">
                        <p class="text-gray-600 text-sm">Export coupon statistics and usage data for analysis.</p>
                        <button onclick="exportStatistics()" class="w-full bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors font-semibold">
                            <i class="fas fa-download mr-2"></i> Export as CSV
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Usage Frequency Chart
            const usageCtx = document.getElementById('usageFrequencyChart');
            if (usageCtx) {
                const usageData = <?= json_encode($stats['usage_frequency']) ?>;
                const labels = usageData.map(item => new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
                const counts = usageData.map(item => parseInt(item.count));
                
                new Chart(usageCtx.getContext('2d'), {
                    type: 'line',
                    data: {
                        labels: labels.length > 0 ? labels : ['No Data'],
                        datasets: [{
                            label: 'Usage Count',
                            data: counts.length > 0 ? counts : [0],
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
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

            // Status Distribution Chart
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                const totalCoupons = <?= $stats['total_coupons'] ?>;
                const activeCoupons = <?= $stats['active_coupons'] ?>;
                const expiredCoupons = <?= $stats['expired_coupons'] ?>;
                const inactiveCoupons = totalCoupons - activeCoupons - expiredCoupons;
                
                new Chart(statusCtx.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Active', 'Expired', 'Inactive'],
                        datasets: [{
                            data: [activeCoupons, expiredCoupons, inactiveCoupons],
                            backgroundColor: [
                                'rgba(34, 197, 94, 0.6)',
                                'rgba(239, 68, 68, 0.6)',
                                'rgba(156, 163, 175, 0.6)'
                            ],
                            borderColor: [
                                'rgb(34, 197, 94)',
                                'rgb(239, 68, 68)',
                                'rgb(156, 163, 175)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        });

        function exportStatistics() {
            const csvContent = "data:text/csv;charset=utf-8," 
                + "Code,Discount,Usage Count,Total Discount Given,Status\n"
                + <?php 
                    $csvRows = [];
                    foreach ($stats['top_coupons'] as $coupon) {
                        $discountText = $coupon['discount_type'] === 'percentage' 
                            ? $coupon['discount_value'] . '%' 
                            : '$' . number_format($coupon['discount_value'], 2);
                        $totalDiscount = floatval($coupon['total_discount_given'] ?? 0);
                        $status = $coupon['valid_to'] < date('Y-m-d H:i:s') ? 'Expired' : ucfirst($coupon['status']);
                        $csvRows[] = '"' . $coupon['code'] . '","' . $discountText . '","' . $coupon['used_count'] . '","' . number_format($totalDiscount, 2) . '","' . $status . '"';
                    }
                    echo json_encode(implode("\n", $csvRows));
                ?>;
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "coupon_statistics_" + new Date().toISOString().split('T')[0] + ".csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
</body>
</html>
<?php
$content = ob_get_clean();
echo $content;
?>

