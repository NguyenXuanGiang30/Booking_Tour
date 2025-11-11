<?php
require_once __DIR__ . '/../../includes/Auth.php';
require_once __DIR__ . '/../../includes/i18n.php';
require_once __DIR__ . '/../../functions/booking_function.php';
require_once __DIR__ . '/../../functions/helper_function.php';

Auth::start();
i18n::init();

$bookingId = $_GET['booking_id'] ?? '';
$message = $_GET['message'] ?? __('payment.error_message') ?? 'Thanh toán thất bại';
$booking = null;

if (!empty($bookingId)) {
    $booking = get_booking_by_id($bookingId);
    if ($booking && $booking['user_id'] !== Auth::getUserId()) {
        $booking = null;
    }
}

$pageTitle = __('payment.error') ?? 'Payment Error - TravelQuest';
$showNavbar = true;
$showFooter = true;
ob_start();
?>
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
    <div class="max-w-2xl w-full">
        <div class="bg-white rounded-xl shadow-lg p-8 text-center">
            <div class="mb-6">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-times text-red-600 text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <?= __('payment.error_title') ?? 'Thanh toán thất bại' ?>
                </h1>
                <p class="text-gray-600 mb-4">
                    <?= htmlspecialchars($message) ?>
                </p>
            </div>

            <?php if ($booking): ?>
                <div class="bg-gray-50 rounded-lg p-6 mb-6 text-left">
                    <h3 class="font-semibold text-gray-900 mb-4"><?= __('payment.booking_info') ?? 'Thông tin đặt tour' ?></h3>
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
                            <span class="text-gray-600"><?= __('payment.total_amount') ?? 'Tổng tiền' ?>:</span>
                            <span class="font-medium text-blue-600"><?= number_format($booking['total_price'], 0, ',', '.') ?> VNĐ</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-yellow-800">
                    <i class="fas fa-info-circle mr-2"></i>
                    <?= __('payment.error_note') ?? 'Đơn đặt tour của bạn vẫn được lưu với trạng thái "Chờ thanh toán". Bạn có thể thử thanh toán lại sau.' ?>
                </p>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <?php if ($booking): ?>
                    <a href="<?= url('/tour/' . $booking['tour_id']) ?>" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                        <i class="fas fa-redo mr-2"></i><?= __('payment.try_again') ?? 'Thử lại thanh toán' ?>
                    </a>
                <?php endif; ?>
                <a href="<?= url('/dashboard') ?>" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
                    <i class="fas fa-user mr-2"></i><?= __('payment.view_dashboard') ?? 'Xem bảng điều khiển' ?>
                </a>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>

