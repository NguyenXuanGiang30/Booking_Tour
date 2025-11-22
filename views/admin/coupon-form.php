<?php
$pageTitle = (isset($coupon) ? 'Edit' : 'Create') . ' Coupon - Admin';
$showNavbar = false;
$showFooter = false;
$admin = Auth::getAdmin();
$isEdit = isset($coupon);
$coupon = $coupon ?? [
    'code' => '',
    'status' => 'active',
    'discount_type' => 'percentage',
    'discount_value' => 0,
    'max_discount' => null,
    'min_amount' => 0,
    'usage_limit' => null,
    'valid_from' => date('Y-m-d\TH:i'),
    'valid_to' => date('Y-m-d\TH:i', strtotime('+1 year')),
    'applicable_tours' => [],
    'description' => ''
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
                    <a href="<?= url('/admin/logout') ?>" class="block px-4 py-2 hover:bg-gray-800 rounded-lg transition-colors mt-8">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </nav>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-900 mb-6"><?= $isEdit ? 'Edit Coupon' : 'Create New Coupon' ?></h1>

                <?php if ($error): ?>
                    <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?= url('/admin/coupons/' . ($isEdit ? 'edit/' . $coupon['id'] : 'create')) ?>" class="bg-white rounded-xl shadow-lg p-8 space-y-6">
                    <?php require_once __DIR__ . '/../../includes/Auth.php'; ?>
                    <input type="hidden" name="csrf_token" value="<?= Auth::generateCsrfToken() ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Coupon Code *</label>
                            <input type="text" 
                                   name="code" 
                                   value="<?= htmlspecialchars($coupon['code']) ?>" 
                                   required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="SUMMER2024"
                                   style="text-transform: uppercase;">
                            <p class="mt-1 text-xs text-gray-500">Will be automatically converted to uppercase</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                            <select name="status" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="active" <?= $coupon['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $coupon['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount Type *</label>
                            <select name="discount_type" id="discount_type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="percentage" <?= $coupon['discount_type'] === 'percentage' ? 'selected' : '' ?>>Percentage (%)</option>
                                <option value="fixed" <?= $coupon['discount_type'] === 'fixed' ? 'selected' : '' ?>>Fixed Amount ($)</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Discount Value *</label>
                            <input type="number" 
                                   step="0.01" 
                                   name="discount_value" 
                                   value="<?= $coupon['discount_value'] ?>" 
                                   required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="10">
                        </div>
                        
                        <div id="max_discount_container" style="<?= $coupon['discount_type'] === 'percentage' ? '' : 'display: none;' ?>">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Max Discount ($)</label>
                            <input type="number" 
                                   step="0.01" 
                                   name="max_discount" 
                                   value="<?= $coupon['max_discount'] ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="50">
                            <p class="mt-1 text-xs text-gray-500">Only for percentage type</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Minimum Amount ($) *</label>
                            <input type="number" 
                                   step="0.01" 
                                   name="min_amount" 
                                   value="<?= $coupon['min_amount'] ?>" 
                                   required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="100">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Usage Limit</label>
                            <input type="number" 
                                   name="usage_limit" 
                                   value="<?= $coupon['usage_limit'] ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                   placeholder="Leave empty for unlimited">
                            <p class="mt-1 text-xs text-gray-500">Leave empty for unlimited usage</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Valid From *</label>
                            <input type="datetime-local" 
                                   name="valid_from" 
                                   value="<?= !empty($coupon['valid_from']) ? date('Y-m-d\TH:i', strtotime($coupon['valid_from'])) : '' ?>" 
                                   required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Valid To *</label>
                            <input type="datetime-local" 
                                   name="valid_to" 
                                   value="<?= !empty($coupon['valid_to']) ? date('Y-m-d\TH:i', strtotime($coupon['valid_to'])) : '' ?>" 
                                   required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Applicable Tours</label>
                        <select name="applicable_tours[]" 
                                multiple 
                                size="10"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">-- All Tours --</option>
                            <?php foreach ($tours as $tour): ?>
                                <option value="<?= $tour['id'] ?>" 
                                        <?= in_array($tour['id'], $coupon['applicable_tours'] ?? []) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tour['title']) ?> - $<?= number_format($tour['price'], 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple tours. Leave empty or select "All Tours" to apply to all tours.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" 
                                  rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                                  placeholder="Optional description for this coupon"><?= htmlspecialchars($coupon['description']) ?></textarea>
                    </div>

                    <div class="flex items-center space-x-4 pt-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                            <i class="fas fa-save mr-2"></i><?= $isEdit ? 'Update Coupon' : 'Create Coupon' ?>
                        </button>
                        <a href="<?= url('/admin/coupons') ?>" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-300 transition-colors font-semibold">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Show/hide max discount field based on discount type
        document.getElementById('discount_type').addEventListener('change', function() {
            const maxDiscountContainer = document.getElementById('max_discount_container');
            if (this.value === 'percentage') {
                maxDiscountContainer.style.display = 'block';
            } else {
                maxDiscountContainer.style.display = 'none';
                document.querySelector('input[name="max_discount"]').value = '';
            }
        });

        // Auto uppercase coupon code
        document.querySelector('input[name="code"]').addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });
    </script>
</body>
</html>
<?php
$content = ob_get_clean();
echo $content;
?>

