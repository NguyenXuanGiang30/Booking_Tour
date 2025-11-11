<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/i18n.php';
require_once __DIR__ . '/../../functions/booking_function.php';
require_once __DIR__ . '/../../functions/helper_function.php';

Auth::start();
i18n::init();

$bookingId = $_GET['booking_id'] ?? '';
$booking = null;

if (!empty($bookingId)) {
    $booking = get_booking_by_id($bookingId);
    // Verify booking belongs to current user
    if ($booking && $booking['user_id'] !== Auth::getUserId()) {
        $booking = null;
    }
}

$pageTitle = __('payment.success') ?? 'Payment Success - TravelQuest';
$showNavbar = true;
$showFooter = true;
ob_start();
?>
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
                    <i class="fas fa-check text-green-600 text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <?= __('payment.success_title') ?? 'Thanh toán thành công!' ?>
                </h1>
                <p class="text-gray-600">
                    <?= __('payment.success_message') ?? 'Cảm ơn bạn đã thanh toán. Đơn đặt tour của bạn đã được xác nhận.' ?>
                </p>
            </div>

            <?php if ($booking): ?>
                <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                    <h3 class="font-semibold text-gray-900 mb-4"><?= __('payment.booking_details') ?? 'Chi tiết đặt tour' ?></h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?= __('payment.booking_id') ?? 'Mã đặt tour' ?>:</span>
                            <span class="font-medium"><?= htmlspecialchars($booking['id']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?= __('payment.tour') ?? 'Tour' ?>:</span>
                            <span class="font-medium"><?= htmlspecialchars($booking['title']) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?= __('payment.booking_date') ?? 'Ngày đặt' ?>:</span>
                            <span class="font-medium"><?= date('d/m/Y', strtotime($booking['booking_date'])) ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?= __('payment.guests') ?? 'Số khách' ?>:</span>
                            <span class="font-medium"><?= $booking['number_of_guests'] ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600"><?= __('payment.total_amount') ?? 'Tổng tiền' ?>:</span>
                            <span class="font-medium text-blue-600"><?= number_format($booking['total_price'], 0, ',', '.') ?> VNĐ</span>
                        </div>
                        <?php if (!empty($booking['vnp_TransactionNo'])): ?>
                            <div class="flex justify-between">
                                <span class="text-gray-600"><?= __('payment.transaction_no') ?? 'Mã giao dịch' ?>:</span>
                                <span class="font-medium"><?= htmlspecialchars($booking['vnp_TransactionNo']) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?= url('/dashboard') ?>" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    <i class="fas fa-user mr-2"></i><?= __('payment.view_dashboard') ?? 'Xem bảng điều khiển' ?>
                </a>
                <a href="<?= url('/tours') ?>" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    <i class="fas fa-map-marker-alt mr-2"></i><?= __('payment.browse_tours') ?? 'Xem thêm tours' ?>
                </a>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

