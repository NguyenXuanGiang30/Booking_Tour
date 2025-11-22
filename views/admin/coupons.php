<?php
$pageTitle = 'Manage Coupons - Admin';
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
                    <a href="<?= url('/admin/tours') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-map-marked-alt mr-2"></i> Tours
                    </a>
                    <a href="<?= url('/admin/bookings') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-calendar-check mr-2"></i> Bookings
                    </a>
                    <a href="<?= url('/admin/users') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
                        <i class="fas fa-users mr-2"></i> Users
                    </a>
                    <a href="<?= url('/admin/coupons') ?>" class="block px-4 py-2 bg-blue-600 rounded-lg">
                        <i class="fas fa-ticket-alt mr-2"></i> Coupons
                    </a>
                    <a href="<?= url('/admin/coupons/statistics') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors">
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
                <h1 class="text-3xl font-bold text-gray-900">Manage Coupons</h1>
                <div class="flex items-center space-x-3">
                    <a href="<?= url('/admin/coupons/statistics') ?>" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition-colors font-semibold">
                        <i class="fas fa-chart-bar mr-2"></i> View Statistics
                    </a>
                    <a href="<?= url('/admin/coupons/create') ?>" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        <i class="fas fa-plus mr-2"></i> Add New Coupon
                    </a>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Search and Filter Form -->
            <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
                <form method="GET" action="<?= url('/admin/coupons') ?>" class="flex items-center space-x-4">
                    <div class="flex-1">
                        <input type="text" 
                               name="search" 
                               value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                               placeholder="Search by code or description..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="active" <?= ($_GET['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($_GET['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                    <?php if (!empty($_GET['search']) || !empty($_GET['status'])): ?>
                        <a href="<?= url('/admin/coupons') ?>" class="bg-gray-600 text-white px-6 py-2 rounded-lg hover:bg-gray-700 transition-colors font-semibold">
                            <i class="fas fa-times mr-2"></i> Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Import CSV Section -->
            <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
                <form method="POST" action="<?= url('/admin/coupons/import') ?>" enctype="multipart/form-data" class="flex items-center space-x-4">
                    <label class="block">
                        <span class="sr-only">Choose CSV file</span>
                        <input type="file" name="file" accept=".csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </label>
                    <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors font-semibold">
                        <i class="fas fa-file-import mr-2"></i> Import CSV
                    </button>
                    <a href="#" onclick="downloadTemplate()" class="text-blue-600 hover:text-blue-700 font-semibold">
                        <i class="fas fa-download mr-2"></i> Download Template
                    </a>
                </form>
            </div>

            <!-- Coupons Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Code</th>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Discount</th>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Min Amount</th>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Valid Period</th>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Usage</th>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Status</th>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Created</th>
                                <th class="py-4 px-6 text-left text-gray-600 font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($coupons)): ?>
                                <tr>
                                    <td colspan="8" class="py-8 px-6 text-center text-gray-500">
                                        No coupons found. <a href="<?= url('/admin/coupons/create') ?>" class="text-blue-600 hover:underline">Create one?</a>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($coupons as $coupon): 
                                    $now = date('Y-m-d H:i:s');
                                    $isExpired = $coupon['valid_to'] < $now;
                                    $isActive = $coupon['status'] === 'active' && !$isExpired;
                                    
                                    $discountText = $coupon['discount_type'] === 'percentage' 
                                        ? $coupon['discount_value'] . '%' 
                                        : '$' . number_format($coupon['discount_value'], 2);
                                    if ($coupon['discount_type'] === 'percentage' && $coupon['max_discount']) {
                                        $discountText .= ' (max $' . number_format($coupon['max_discount'], 2) . ')';
                                    }
                                    
                                    $usageText = $coupon['used_count'];
                                    if ($coupon['usage_limit']) {
                                        $usageText .= ' / ' . $coupon['usage_limit'];
                                    } else {
                                        $usageText .= ' / ∞';
                                    }
                                ?>
                                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                                        <td class="py-4 px-6">
                                            <div class="font-semibold text-gray-900"><?= htmlspecialchars($coupon['code']) ?></div>
                                            <?php if (!empty($coupon['description'])): ?>
                                                <div class="text-sm text-gray-500"><?= htmlspecialchars(substr($coupon['description'], 0, 50)) ?><?= strlen($coupon['description']) > 50 ? '...' : '' ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-6 font-semibold text-green-600"><?= $discountText ?></td>
                                        <td class="py-4 px-6">$<?= number_format($coupon['min_amount'], 2) ?></td>
                                        <td class="py-4 px-6">
                                            <div class="text-sm">
                                                <div><?= date('M j, Y', strtotime($coupon['valid_from'])) ?></div>
                                                <div class="text-gray-500">to <?= date('M j, Y', strtotime($coupon['valid_to'])) ?></div>
                                                <?php if ($isExpired): ?>
                                                    <span class="text-red-600 text-xs">Expired</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                                <?= $usageText ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="px-3 py-1 rounded-full text-sm font-semibold <?= $isActive ? 'bg-green-100 text-green-800' : ($isExpired ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') ?>">
                                                <?= $isExpired ? 'Expired' : ucfirst($coupon['status']) ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-sm text-gray-500">
                                            <?= date('M j, Y', strtotime($coupon['created_at'])) ?>
                                        </td>
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-2">
                                                <a href="<?= url('/admin/coupons/edit/' . $coupon['id']) ?>" class="text-blue-600 hover:text-blue-800" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= url('/admin/coupons/delete/' . $coupon['id']) ?>" 
                                                   onclick="return confirm('Are you sure you want to delete this coupon?')" 
                                                   class="text-red-600 hover:text-red-800" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <div class="px-6 py-4 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-600">
                                Showing <?= $pagination['start_item'] ?> to <?= $pagination['end_item'] ?> of <?= $pagination['total_items'] ?> coupons
                            </div>
                            <div class="flex items-center space-x-2">
                                <?php if ($pagination['current_page'] > 1): ?>
                                    <a href="<?= url('/admin/coupons?' . http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] - 1]))) ?>" 
                                       class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                        Previous
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <?php if ($i == $pagination['current_page']): ?>
                                        <span class="px-4 py-2 bg-blue-600 text-white rounded-lg"><?= $i ?></span>
                                    <?php elseif ($i == 1 || $i == $pagination['total_pages'] || abs($i - $pagination['current_page']) <= 2): ?>
                                        <a href="<?= url('/admin/coupons?' . http_build_query(array_merge($_GET, ['page' => $i]))) ?>" 
                                           class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                            <?= $i ?>
                                        </a>
                                    <?php elseif (abs($i - $pagination['current_page']) == 3): ?>
                                        <span class="px-2">...</span>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                
                                <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                    <a href="<?= url('/admin/coupons?' . http_build_query(array_merge($_GET, ['page' => $pagination['current_page'] + 1]))) ?>" 
                                       class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                        Next
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function downloadTemplate() {
            const csvContent = "data:text/csv;charset=utf-8," 
                + "Code,Status,Discount Type,Discount Value,Max Discount,Min Amount,Usage Limit,Valid From,Valid To,Applicable Tours (comma-separated IDs),Description\n"
                + "SUMMER2024,active,percentage,10,50,100,100,2024-01-01 00:00:00,2024-12-31 23:59:59,,Summer promotion\n"
                + "WELCOME20,active,fixed,20,,50,500,2024-01-01 00:00:00,2024-12-31 23:59:59,,Welcome discount\n";
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "coupon_template.csv");
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

