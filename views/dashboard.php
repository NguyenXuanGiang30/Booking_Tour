<?php
$pageTitle = 'Dashboard - TravelQuest';
$showNavbar = true;
$showFooter = false;
$view = $view ?? 'profile';
$success = $_GET['success'] ?? '';
$error = $error ?? '';
ob_start();
?>
<div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-4xl font-bold text-gray-900 mb-8">My Dashboard</h1>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <nav class="space-y-2">
                        <a href="<?= url('/dashboard?view=profile') ?>" class="block px-4 py-2 rounded-lg <?= $view === 'profile' ? 'bg-blue-100 text-blue-600' : 'text-gray-600 hover:bg-gray-100' ?>">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <a href="<?= url('/dashboard?view=bookings') ?>" class="block px-4 py-2 rounded-lg <?= $view === 'bookings' ? 'bg-blue-100 text-blue-600' : 'text-gray-600 hover:bg-gray-100' ?>">
                            <i class="fas fa-calendar-check mr-2"></i> Bookings
                        </a>
                        <a href="<?= url('/dashboard?view=wishlist') ?>" class="block px-4 py-2 rounded-lg <?= $view === 'wishlist' ? 'bg-blue-100 text-blue-600' : 'text-gray-600 hover:bg-gray-100' ?>">
                            <i class="fas fa-heart mr-2"></i> Wishlist
                        </a>
                        <a href="<?= url('/dashboard?view=payments') ?>" class="block px-4 py-2 rounded-lg <?= $view === 'payments' ? 'bg-blue-100 text-blue-600' : 'text-gray-600 hover:bg-gray-100' ?>">
                            <i class="fas fa-credit-card mr-2"></i> Payments
                        </a>
                        <a href="<?= url('/dashboard?view=reviews') ?>" class="block px-4 py-2 rounded-lg <?= $view === 'reviews' ? 'bg-blue-100 text-blue-600' : 'text-gray-600 hover:bg-gray-100' ?>">
                            <i class="fas fa-star mr-2"></i> Reviews
                        </a>
                        <a href="<?= url('/dashboard?view=settings') ?>" class="block px-4 py-2 rounded-lg <?= $view === 'settings' ? 'bg-blue-100 text-blue-600' : 'text-gray-600 hover:bg-gray-100' ?>">
                            <i class="fas fa-cog mr-2"></i> Settings
                        </a>
                    </nav>
                </div>
            </div>

            <div class="lg:col-span-3">
                <?php if ($success): ?>
                    <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($view === 'profile'): ?>
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Profile Information</h2>
                        <form method="POST" action="<?= url('/dashboard/profile') ?>" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Birthday</label>
                                    <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                                <textarea name="address" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">Save Changes</button>
                        </form>
                    </div>
                <?php elseif ($view === 'bookings'): ?>
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">My Bookings</h2>
                        <?php if (empty($bookings)): ?>
                            <p class="text-gray-600">No bookings yet. <a href="<?= url('/tours') ?>" class="text-blue-600 hover:underline">Explore tours</a></p>
                        <?php else: ?>
                            <div class="space-y-4">
                                <?php foreach ($bookings as $booking): ?>
                                    <div class="border border-gray-200 rounded-lg p-6">
                                        <div class="flex items-start justify-between">
                                            <div class="flex space-x-4">
                                                <?php if (!empty($booking['images']) && is_array($booking['images']) && !empty($booking['images'][0])): ?>
                                                    <img src="<?= htmlspecialchars($booking['images'][0]) ?>" alt="<?= htmlspecialchars($booking['title']) ?>" class="w-24 h-24 object-cover rounded-lg">
                                                <?php endif; ?>
                                                <div>
                                                    <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($booking['title']) ?></h3>
                                                    <p class="text-gray-600"><?= htmlspecialchars($booking['location']) ?></p>
                                                    <p class="text-sm text-gray-500 mt-2">Booking Date: <?= date('F j, Y', strtotime($booking['booking_date'])) ?></p>
                                                    <p class="text-sm text-gray-500">Guests: <?= $booking['number_of_guests'] ?></p>
                                                    <?php if (!empty($booking['payment_method'])): ?>
                                                        <p class="text-sm text-gray-500">Payment: <?= htmlspecialchars($booking['payment_method']) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-2xl font-bold text-blue-600">$<?= number_format($booking['total_price']) ?></div>
                                                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold mt-2 <?= $booking['status'] === 'paid' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                    <?= ucfirst($booking['status']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                                <?php
                                require_once __DIR__ . '/../functions/pagination_function.php';
                                $queryParams = ['view' => 'bookings'];
                                $baseUrl = url('/dashboard');
                                include __DIR__ . '/components/pagination.php';
                                ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php elseif ($view === 'wishlist'): ?>
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">My Wishlist</h2>
                        <?php if (empty($wishlist)): ?>
                            <p class="text-gray-600">Your wishlist is empty. <a href="<?= url('/tours') ?>" class="text-blue-600 hover:underline">Explore tours</a></p>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <?php foreach ($wishlist as $item): ?>
                                    <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow cursor-pointer" onclick="window.location.href='<?= url('/tour/' . $item['tour_id']) ?>'">
                                        <img src="<?= htmlspecialchars(!empty($item['images']) && is_array($item['images']) && !empty($item['images'][0]) ? $item['images'][0] : 'https://images.pexels.com/photos/346885/pexels-photo-346885.jpeg') ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-full h-48 object-cover">
                                        <div class="p-4">
                                            <h3 class="text-lg font-bold text-gray-900 mb-2"><?= htmlspecialchars($item['title']) ?></h3>
                                            <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($item['location']) ?></p>
                                            <div class="flex items-center justify-between">
                                                <span class="text-xl font-bold text-blue-600">$<?= number_format($item['price']) ?></span>
                                                <button onclick="event.stopPropagation(); removeWishlist('<?= $item['tour_id'] ?>')" class="text-red-500 hover:text-red-700">
                                                    <i class="fas fa-heart"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                                <?php
                                require_once __DIR__ . '/../functions/pagination_function.php';
                                $queryParams = ['view' => 'wishlist'];
                                $baseUrl = url('/dashboard');
                                include __DIR__ . '/components/pagination.php';
                                ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php elseif ($view === 'payments'): ?>
                    <div class="space-y-6">
                        <!-- Payment Methods Section -->
                        <div class="bg-white rounded-xl shadow-lg p-8">
                            <div class="flex items-center justify-between mb-6">
                                <h2 class="text-2xl font-bold text-gray-900">Payment Methods</h2>
                                <button onclick="openPaymentMethodModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                                    <i class="fas fa-plus mr-2"></i> Add Payment Method
                                </button>
                            </div>
                            
                            <?php if (empty($paymentMethods)): ?>
                                <p class="text-gray-600 mb-4">No payment methods saved. Add one to make payments faster.</p>
                            <?php else: ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($paymentMethods as $method): ?>
                                        <div class="border border-gray-200 rounded-lg p-4 <?= $method['is_default'] ? 'border-blue-500 bg-blue-50' : '' ?>">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <div class="flex items-center space-x-2 mb-2">
                                                        <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($method['name']) ?></h4>
                                                        <?php if ($method['is_default']): ?>
                                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-semibold">Default</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <p class="text-sm text-gray-600 capitalize"><?= htmlspecialchars($method['type']) ?></p>
                                                    <?php if (!empty($method['last_four'])): ?>
                                                        <p class="text-sm text-gray-500">**** <?= htmlspecialchars($method['last_four']) ?></p>
                                                    <?php endif; ?>
                                                    <?php if (!empty($method['expiry_date'])): ?>
                                                        <p class="text-sm text-gray-500">Expires: <?= htmlspecialchars($method['expiry_date']) ?></p>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <?php if (!$method['is_default']): ?>
                                                        <form method="POST" action="<?= url('/dashboard/payment-method/set-default') ?>" class="inline">
                                                            <input type="hidden" name="id" value="<?= $method['id'] ?>">
                                                            <button type="submit" class="text-blue-600 hover:text-blue-700 text-sm" title="Set as default">
                                                                <i class="fas fa-star"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <button onclick="editPaymentMethod(<?= htmlspecialchars(json_encode($method, JSON_HEX_APOS | JSON_HEX_QUOT)) ?>)" class="text-blue-600 hover:text-blue-700" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form method="POST" action="<?= url('/dashboard/payment-method/delete') ?>" class="inline" onsubmit="return confirm('Are you sure you want to delete this payment method?')">
                                                        <input type="hidden" name="id" value="<?= $method['id'] ?>">
                                                        <button type="submit" class="text-red-600 hover:text-red-700" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Payment Statistics -->
                        <?php if (!empty($paymentStats) && $paymentStats['total_payments'] > 0): ?>
                            <div class="bg-white rounded-xl shadow-lg p-8">
                                <h2 class="text-2xl font-bold text-gray-900 mb-6">Payment Statistics</h2>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="bg-blue-50 rounded-lg p-4">
                                        <p class="text-gray-600 text-sm">Total Payments</p>
                                        <p class="text-2xl font-bold text-blue-600"><?= $paymentStats['total_payments'] ?></p>
                                    </div>
                                    <div class="bg-green-50 rounded-lg p-4">
                                        <p class="text-gray-600 text-sm">Total Spent</p>
                                        <p class="text-2xl font-bold text-green-600">$<?= number_format($paymentStats['total_spent'], 2) ?></p>
                                    </div>
                                    <div class="bg-purple-50 rounded-lg p-4">
                                        <p class="text-gray-600 text-sm">Average Payment</p>
                                        <p class="text-2xl font-bold text-purple-600">$<?= number_format($paymentStats['average_payment'], 2) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Payment History -->
                        <div class="bg-white rounded-xl shadow-lg p-8">
                            <h2 class="text-2xl font-bold text-gray-900 mb-6">Payment History</h2>
                            <?php if (empty($payments)): ?>
                                <p class="text-gray-600">No payment history yet. <a href="<?= url('/tours') ?>" class="text-blue-600 hover:underline">Explore tours</a></p>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($payments as $payment): ?>
                                        <div class="border border-gray-200 rounded-lg p-6">
                                            <div class="flex items-start justify-between">
                                                <div class="flex space-x-4 flex-1">
                                                    <?php if (!empty($payment['images']) && is_array($payment['images']) && !empty($payment['images'][0])): ?>
                                                        <img src="<?= htmlspecialchars($payment['images'][0]) ?>" alt="<?= htmlspecialchars($payment['title']) ?>" class="w-24 h-24 object-cover rounded-lg">
                                                    <?php endif; ?>
                                                    <div class="flex-1">
                                                        <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($payment['title']) ?></h3>
                                                        <p class="text-gray-600"><?= htmlspecialchars($payment['location']) ?></p>
                                                        <div class="mt-2 space-y-1">
                                                            <p class="text-sm text-gray-500">Payment Date: <?= date('F j, Y', strtotime($payment['created_at'])) ?></p>
                                                            <p class="text-sm text-gray-500">Booking Date: <?= date('F j, Y', strtotime($payment['booking_date'])) ?></p>
                                                            <p class="text-sm text-gray-500">Guests: <?= $payment['number_of_guests'] ?></p>
                                                            <?php if (!empty($payment['payment_method'])): ?>
                                                                <p class="text-sm text-gray-500">Payment Method: <?= htmlspecialchars($payment['payment_method']) ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-2xl font-bold text-green-600">$<?= number_format($payment['total_price'], 2) ?></div>
                                                    <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold mt-2 bg-green-100 text-green-800">
                                                        Paid
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                                    <?php
                                    require_once __DIR__ . '/../functions/pagination_function.php';
                                    $queryParams = ['view' => 'payments'];
                                    $baseUrl = url('/dashboard');
                                    include __DIR__ . '/components/pagination.php';
                                    ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($view === 'reviews'): ?>
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">My Reviews</h2>
                        
                        <!-- Tours available for review -->
                        <?php if (!empty($toursForReview)): ?>
                            <div class="mb-8">
                                <h3 class="text-xl font-semibold text-gray-900 mb-4">Write a Review</h3>
                                <div class="space-y-4">
                                    <?php foreach ($toursForReview as $tour): ?>
                                        <div class="border border-gray-200 rounded-lg p-6">
                                            <div class="flex items-start justify-between">
                                                <div class="flex space-x-4 flex-1">
                                                    <?php if (!empty($tour['images']) && is_array($tour['images']) && !empty($tour['images'][0])): ?>
                                                        <img src="<?= htmlspecialchars($tour['images'][0]) ?>" alt="<?= htmlspecialchars($tour['title']) ?>" class="w-20 h-20 object-cover rounded-lg">
                                                    <?php endif; ?>
                                                    <div class="flex-1">
                                                        <h4 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($tour['title']) ?></h4>
                                                        <p class="text-gray-600 text-sm"><?= htmlspecialchars($tour['location']) ?></p>
                                                    </div>
                                                </div>
                                                <button onclick="openReviewModal('<?= $tour['id'] ?>', <?= json_encode($tour['title']) ?>)" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-semibold">
                                                    Write Review
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- User's reviews -->
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">Your Reviews</h3>
                            <?php if (empty($reviews)): ?>
                                <p class="text-gray-600">You haven't written any reviews yet.</p>
                            <?php else: ?>
                                <div class="space-y-4">
                                    <?php foreach ($reviews as $review): ?>
                                        <div class="border border-gray-200 rounded-lg p-6">
                                            <div class="flex items-start space-x-4">
                                                <?php if (!empty($review['images']) && is_array($review['images']) && !empty($review['images'][0])): ?>
                                                    <img src="<?= htmlspecialchars($review['images'][0]) ?>" alt="<?= htmlspecialchars($review['title']) ?>" class="w-20 h-20 object-cover rounded-lg">
                                                <?php endif; ?>
                                                <div class="flex-1">
                                                    <div class="flex items-center justify-between mb-2">
                                                        <h4 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($review['title']) ?></h4>
                                                        <div class="flex items-center space-x-1">
                                                            <?php for ($i = 0; $i < 5; $i++): ?>
                                                                <i class="fas fa-star <?= $i < $review['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                                            <?php endfor; ?>
                                                        </div>
                                                    </div>
                                                    <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($review['location']) ?></p>
                                                    <p class="text-gray-700 mb-2"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                                    <p class="text-sm text-gray-500">Reviewed on <?= date('F j, Y', strtotime($review['created_at'])) ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                                    <?php
                                    require_once __DIR__ . '/../functions/pagination_function.php';
                                    $queryParams = ['view' => 'reviews'];
                                    $baseUrl = url('/dashboard');
                                    include __DIR__ . '/components/pagination.php';
                                    ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php elseif ($view === 'settings'): ?>
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Settings</h2>
                        
                        <!-- Change Password -->
                        <div class="mb-8">
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">Change Password</h3>
                            <form method="POST" action="<?= url('/dashboard/password') ?>" class="space-y-4 max-w-md">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                    <input type="password" name="current_password" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                    <input type="password" name="new_password" required minlength="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <p class="text-xs text-gray-500 mt-1">Password must be at least 6 characters</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                                    <input type="password" name="confirm_password" required minlength="6" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                </div>
                                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                                    Change Password
                                </button>
                            </form>
                        </div>
                        
                        <!-- Account Information -->
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">Account Information</h3>
                            <div class="space-y-3">
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <span class="text-gray-600">Email</span>
                                    <span class="font-semibold"><?= htmlspecialchars($user['email'] ?? '') ?></span>
                                </div>
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <span class="text-gray-600">Member Since</span>
                                    <span class="font-semibold"><?= date('F j, Y', strtotime($user['created_at'] ?? 'now')) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Payment Method Modal -->
<div id="paymentMethodModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold text-gray-900" id="paymentMethodModalTitle">Add Payment Method</h3>
            <button onclick="closePaymentMethodModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="paymentMethodForm" method="POST" action="<?= url('/dashboard/payment-method/add') ?>" class="space-y-4">
            <input type="hidden" name="id" id="paymentMethodId">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Type</label>
                <select name="type" id="paymentMethodType" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" onchange="toggleCardFields()">
                    <option value="card">Credit/Debit Card</option>
                    <option value="bank_account">Bank Account</option>
                    <option value="paypal">PayPal</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Name / Card Holder Name</label>
                <input type="text" name="name" id="paymentMethodName" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="John Doe">
            </div>
            <div id="cardFields">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Last 4 Digits</label>
                    <input type="text" name="last_four" id="paymentMethodLastFour" maxlength="4" pattern="[0-9]{4}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="1234">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Expiry Date (MM/YYYY)</label>
                    <input type="text" name="expiry_date" id="paymentMethodExpiry" pattern="(0[1-9]|1[0-2])\/[0-9]{4}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="12/2025">
                </div>
            </div>
            <div>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" name="is_default" id="paymentMethodIsDefault" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <span class="text-sm text-gray-700">Set as default payment method</span>
                </label>
            </div>
            <div class="flex items-center space-x-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                    <span id="paymentMethodSubmitText">Add</span> Payment Method
                </button>
                <button type="button" onclick="closePaymentMethodModal()" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Review Modal -->
<div id="reviewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold text-gray-900">Write a Review</h3>
            <button onclick="closeReviewModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="reviewForm" method="POST" action="<?= url('/dashboard/review') ?>" class="space-y-4">
            <input type="hidden" name="tour_id" id="reviewTourId">
            <div>
                <p class="text-gray-700 font-semibold mb-2" id="reviewTourTitle"></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                <div class="flex items-center space-x-2" id="ratingStars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <button type="button" onclick="setRating(<?= $i ?>)" class="text-3xl text-gray-300 hover:text-yellow-400 transition-colors rating-star" data-rating="<?= $i ?>">
                            <i class="fas fa-star"></i>
                        </button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="reviewRating" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Comment</label>
                <textarea name="comment" id="reviewComment" rows="4" required placeholder="Write your review here..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex items-center space-x-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                    Submit Review
                </button>
                <button type="button" onclick="closeReviewModal()" class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-semibold">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function removeWishlist(tourId) {
    if (confirm('Remove from wishlist?')) {
        fetch('<?= url('/api/wishlist/remove') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'tour_id=' + tourId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error || 'Failed to remove from wishlist');
            }
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}

let currentRating = 0;

function openReviewModal(tourId, tourTitle) {
    document.getElementById('reviewTourId').value = tourId;
    document.getElementById('reviewTourTitle').textContent = tourTitle;
    document.getElementById('reviewModal').classList.remove('hidden');
    currentRating = 0;
    updateRatingDisplay();
    document.getElementById('reviewComment').value = '';
}

function closeReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
    currentRating = 0;
    document.getElementById('reviewRating').value = '';
    document.getElementById('reviewComment').value = '';
}

function setRating(rating) {
    currentRating = rating;
    document.getElementById('reviewRating').value = rating;
    updateRatingDisplay();
}

function updateRatingDisplay() {
    const stars = document.querySelectorAll('.rating-star');
    stars.forEach((star, index) => {
        const rating = index + 1;
        const icon = star.querySelector('i');
        if (rating <= currentRating) {
            icon.classList.remove('text-gray-300');
            icon.classList.add('text-yellow-400');
        } else {
            icon.classList.remove('text-yellow-400');
            icon.classList.add('text-gray-300');
        }
    });
}

// Close modal when clicking outside
document.getElementById('reviewModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeReviewModal();
    }
});

// Payment Method Modal Functions
function openPaymentMethodModal(method = null) {
    const modal = document.getElementById('paymentMethodModal');
    const form = document.getElementById('paymentMethodForm');
    const title = document.getElementById('paymentMethodModalTitle');
    const submitText = document.getElementById('paymentMethodSubmitText');
    
    if (method) {
        // Edit mode
        title.textContent = 'Edit Payment Method';
        submitText.textContent = 'Update';
        form.action = '<?= url('/dashboard/payment-method/update') ?>';
        document.getElementById('paymentMethodId').value = method.id;
        document.getElementById('paymentMethodType').value = method.type;
        document.getElementById('paymentMethodName').value = method.name;
        document.getElementById('paymentMethodLastFour').value = method.last_four || '';
        document.getElementById('paymentMethodExpiry').value = method.expiry_date || '';
        document.getElementById('paymentMethodIsDefault').checked = method.is_default == 1;
    } else {
        // Add mode
        title.textContent = 'Add Payment Method';
        submitText.textContent = 'Add';
        form.action = '<?= url('/dashboard/payment-method/add') ?>';
        form.reset();
        document.getElementById('paymentMethodId').value = '';
    }
    
    toggleCardFields();
    modal.classList.remove('hidden');
}

function closePaymentMethodModal() {
    document.getElementById('paymentMethodModal').classList.add('hidden');
    document.getElementById('paymentMethodForm').reset();
    document.getElementById('paymentMethodId').value = '';
}

function toggleCardFields() {
    const type = document.getElementById('paymentMethodType').value;
    const cardFields = document.getElementById('cardFields');
    const lastFour = document.getElementById('paymentMethodLastFour');
    const expiry = document.getElementById('paymentMethodExpiry');
    
    if (type === 'card') {
        cardFields.style.display = 'block';
        lastFour.required = true;
        expiry.required = true;
    } else {
        cardFields.style.display = 'none';
        lastFour.required = false;
        expiry.required = false;
        lastFour.value = '';
        expiry.value = '';
    }
}

function editPaymentMethod(method) {
    openPaymentMethodModal(method);
}

// Close payment method modal when clicking outside
document.getElementById('paymentMethodModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closePaymentMethodModal();
    }
});
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
