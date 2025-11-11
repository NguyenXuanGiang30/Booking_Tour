<?php
$pageTitle = htmlspecialchars($tour['title']) . ' - TravelQuest';
$showNavbar = true;
$showFooter = true;
$isLoggedIn = Auth::isLoggedIn();
ob_start();
?>
<div class="min-h-screen bg-gray-50">
    <div class="relative h-[500px] bg-gray-900">
        <img src="<?= htmlspecialchars(!empty($tour['images']) && is_array($tour['images']) && !empty($tour['images'][0]) ? $tour['images'][0] : 'https://images.pexels.com/photos/346885/pexels-photo-346885.jpeg') ?>" alt="<?= htmlspecialchars($tour['title']) ?>" class="w-full h-full object-cover opacity-90" id="mainImage">
        
        <?php if (!empty($tour['images']) && is_array($tour['images']) && count($tour['images']) > 1): ?>
            <button onclick="prevImage()" class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full transition-colors">
                <i class="fas fa-chevron-left text-gray-900"></i>
            </button>
            <button onclick="nextImage()" class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/80 hover:bg-white p-2 rounded-full transition-colors">
                <i class="fas fa-chevron-right text-gray-900"></i>
            </button>
        <?php endif; ?>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 text-gray-600 mb-2">
                                <i class="fas fa-map-marker-alt"></i>
                                <span class="text-lg"><?= htmlspecialchars($tour['location']) ?></span>
                            </div>
                            <h1 class="text-4xl font-bold text-gray-900 mb-4"><?= htmlspecialchars($tour['title']) ?></h1>
                            <div class="flex items-center space-x-6">
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-star text-yellow-400"></i>
                                    <span class="font-semibold"><?= number_format($tour['rating'] ?? 0, 1) ?></span>
                                    <span class="text-gray-600">(<?= $tour['total_reviews'] ?? 0 ?> reviews)</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="far fa-clock text-gray-600"></i>
                                    <span class="text-gray-600"><?= $tour['duration'] ?> days</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <i class="fas fa-users text-gray-600"></i>
                                    <span class="text-gray-600">Max <?= $tour['max_guests'] ?> guests</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <?php if ($isLoggedIn): ?>
                                <button onclick="toggleWishlist('<?= $tour['id'] ?>')" class="p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors" id="wishlistBtn">
                                    <i class="fas fa-heart text-gray-600"></i>
                                </button>
                            <?php endif; ?>
                            <button class="p-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-share-alt text-gray-600"></i>
                            </button>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">About This Tour</h2>
                        <p class="text-gray-600 leading-relaxed"><?= nl2br(htmlspecialchars($tour['description'])) ?></p>
                    </div>
                </div>

                <?php if (!empty($tour['itinerary']) && is_array($tour['itinerary'])): ?>
                <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Itinerary</h2>
                    <div class="space-y-6">
                        <?php 
                        $dayNum = 1;
                        foreach ($tour['itinerary'] as $day): 
                            // Handle both object format and string format
                            if (is_array($day) && isset($day['day'])) {
                                $dayNumber = $day['day'];
                                $dayTitle = $day['title'] ?? '';
                                $dayDescription = $day['description'] ?? '';
                            } elseif (is_array($day) && isset($day['title'])) {
                                $dayNumber = $dayNum;
                                $dayTitle = $day['title'];
                                $dayDescription = $day['description'] ?? '';
                            } elseif (is_string($day)) {
                                $dayNumber = $dayNum;
                                $dayTitle = "Day $dayNumber";
                                $dayDescription = $day;
                            } else {
                                continue;
                            }
                            $dayNum++;
                        ?>
                            <div class="flex space-x-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-bold text-sm">Day <?= $dayNumber ?></span>
                                </div>
                                <div class="flex-1">
                                    <?php if (!empty($dayTitle) && $dayTitle !== "Day $dayNumber"): ?>
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2"><?= htmlspecialchars($dayTitle) ?></h3>
                                    <?php endif; ?>
                                    <?php if (!empty($dayDescription)): ?>
                                        <p class="text-gray-600"><?= nl2br(htmlspecialchars($dayDescription)) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="bg-white rounded-xl shadow-lg p-8 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <?php if (!empty($tour['included']) && is_array($tour['included'])): ?>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">What's Included</h2>
                            <ul class="space-y-3">
                                <?php foreach ($tour['included'] as $item): ?>
                                    <?php if (!empty(trim($item))): ?>
                                    <li class="flex items-start space-x-3">
                                        <i class="fas fa-check text-green-500 flex-shrink-0 mt-0.5"></i>
                                        <span class="text-gray-600"><?= htmlspecialchars($item) ?></span>
                                    </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($tour['excluded']) && is_array($tour['excluded'])): ?>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">What's Not Included</h2>
                            <ul class="space-y-3">
                                <?php foreach ($tour['excluded'] as $item): ?>
                                    <?php if (!empty(trim($item))): ?>
                                    <li class="flex items-start space-x-3">
                                        <i class="fas fa-times text-red-500 flex-shrink-0 mt-0.5"></i>
                                        <span class="text-gray-600"><?= htmlspecialchars($item) ?></span>
                                    </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($reviews) && is_array($reviews)): ?>
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Reviews (<?= $tour['total_reviews'] ?? 0 ?>)</h2>
                    <div class="space-y-6">
                        <?php foreach ($reviews as $review): ?>
                            <div class="border-b border-gray-200 pb-6 last:border-0">
                                <div class="flex items-start space-x-4">
                                    <img src="<?= htmlspecialchars($review['avatar_url'] ?? 'https://images.pexels.com/photos/774909/pexels-photo-774909.jpeg') ?>" alt="<?= htmlspecialchars($review['full_name']) ?>" class="w-12 h-12 rounded-full object-cover">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="font-semibold text-gray-900"><?= htmlspecialchars($review['full_name'] ?? 'Anonymous') ?></h4>
                                            <div class="flex items-center space-x-1">
                                                <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                                    <i class="fas fa-star text-yellow-400"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <p class="text-gray-600"><?= htmlspecialchars($review['comment']) ?></p>
                                        <p class="text-sm text-gray-400 mt-2"><?= date('F j, Y', strtotime($review['created_at'])) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg p-6 sticky top-24">
                    <div class="mb-6">
                        <div class="flex items-baseline space-x-2 mb-2">
                            <span class="text-4xl font-bold text-blue-600">$<?= number_format($tour['price']) ?></span>
                            <span class="text-gray-600">/ person</span>
                        </div>
                        <p class="text-sm text-gray-500">Best price guarantee</p>
                    </div>

                    <form id="bookingForm" class="space-y-4 mb-6">
                        <input type="hidden" name="tour_id" value="<?= $tour['id'] ?>">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Select Date</label>
                            <div class="relative">
                                <i class="fas fa-calendar absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <input type="date" name="booking_date" id="bookingDate" required min="<?= date('Y-m-d') ?>" onchange="updateDateAvailability()" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div id="dateAvailability" class="mt-2 text-sm"></div>
                            <div id="availabilityCalendar" class="mt-4 hidden">
                                <div class="text-sm font-medium text-gray-700 mb-2">Available Dates</div>
                                <div id="calendarGrid" class="grid grid-cols-7 gap-1 text-xs"></div>
                            </div>
                        </div>
                        
                        <!-- Coupon Code -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Promo Code</label>
                            <div class="flex gap-2">
                                <input type="text" name="coupon_code" id="couponCode" placeholder="Enter promo code" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                                <button type="button" onclick="validateCoupon()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors text-sm font-medium">
                                    Apply
                                </button>
                            </div>
                            <div id="couponMessage" class="mt-2 text-sm"></div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Number of Guests</label>
                            <div class="relative">
                                <i class="fas fa-users absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                                <select name="number_of_guests" id="guestsSelect" onchange="updateTotal()" class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent appearance-none">
                                    <?php for ($i = 1; $i <= $tour['max_guests']; $i++): ?>
                                        <option value="<?= $i ?>"><?= $i ?> <?= $i === 1 ? 'Guest' : 'Guests' ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <?php if ($isLoggedIn): ?>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                <div class="space-y-2">
                                    <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                        <input type="radio" name="payment_type" value="vnpay" checked class="mr-3 text-blue-600 focus:ring-blue-500">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <img src="https://sandbox.vnpayment.vn/apis/assets/images/logo.svg" alt="VNPay" class="h-6 mr-2">
                                                <span class="font-medium">VNPay</span>
                                            </div>
                                            <p class="text-xs text-gray-500 mt-1">Thanh toán qua VNPay (ATM, Visa, Mastercard)</p>
                                        </div>
                                    </label>
                                    
                                    <?php if (!empty($paymentMethods)): ?>
                                        <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                                            <input type="radio" name="payment_type" value="saved" class="mr-3 text-blue-600 focus:ring-blue-500">
                                            <div class="flex-1">
                                                <span class="font-medium">Saved Payment Method</span>
                                                <select name="payment_method_id" id="paymentMethodSelect" class="w-full mt-2 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm" disabled>
                                                    <option value="">Select a payment method</option>
                                                    <?php foreach ($paymentMethods as $method): ?>
                                                        <option value="<?= htmlspecialchars($method['id']) ?>" <?= $defaultPaymentMethod && $defaultPaymentMethod['id'] === $method['id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars(ucfirst($method['type'])) ?>
                                                            <?php if (!empty($method['last_four'])): ?>
                                                                - ****<?= htmlspecialchars($method['last_four']) ?>
                                                            <?php endif; ?>
                                                            <?php if ($method['is_default']): ?>
                                                                (Default)
                                                            <?php endif; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </label>
                                        <p class="text-xs text-gray-500">
                                            <a href="<?= url('/dashboard?view=payments') ?>" class="text-blue-600 hover:underline">Manage payment methods</a>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </form>

                    <div class="border-t border-gray-200 py-4 space-y-2">
                        <div class="flex justify-between text-gray-600">
                            <span>$<span id="pricePerPerson"><?= number_format($tour['price']) ?></span> x <span id="guestsCount">1</span> guests</span>
                            <span>$<span id="totalPrice"><?= number_format($tour['price']) ?></span></span>
                        </div>
                        <div id="discountRow" class="flex justify-between text-green-600 hidden">
                            <span>Discount</span>
                            <span>-$<span id="discountAmount">0</span></span>
                        </div>
                        <div class="flex justify-between font-bold text-gray-900 text-lg">
                            <span>Total</span>
                            <span>$<span id="finalTotal"><?= number_format($tour['price']) ?></span></span>
                        </div>
                    </div>

                    <button onclick="bookNow()" class="w-full bg-blue-600 text-white py-4 rounded-lg hover:bg-blue-700 transition-colors font-semibold text-lg">
                        Book Now
                    </button>

                    <p class="text-sm text-gray-500 text-center mt-4">You won't be charged yet</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const tourId = '<?= $tour['id'] ?>';
const tourPrice = <?= floatval($tour['price'] ?? 0) ?>;
const tourImages = <?= json_encode(!empty($tour['images']) && is_array($tour['images']) ? $tour['images'] : ['https://images.pexels.com/photos/346885/pexels-photo-346885.jpeg']) ?>;
let currentImageIndex = 0;
let currentPrice = tourPrice;
let appliedCoupon = null;
let discountAmount = 0;
let availabilityCalendar = {};

function nextImage() {
    if (tourImages.length > 1) {
        currentImageIndex = (currentImageIndex + 1) % tourImages.length;
        document.getElementById('mainImage').src = tourImages[currentImageIndex];
    }
}

function prevImage() {
    if (tourImages.length > 1) {
        currentImageIndex = (currentImageIndex - 1 + tourImages.length) % tourImages.length;
        document.getElementById('mainImage').src = tourImages[currentImageIndex];
    }
}

function updateTotal() {
    const guests = parseInt(document.getElementById('guestsSelect').value);
    const total = currentPrice * guests;
    const finalTotal = total - discountAmount;
    
    document.getElementById('guestsCount').textContent = guests;
    document.getElementById('pricePerPerson').textContent = currentPrice.toLocaleString();
    document.getElementById('totalPrice').textContent = total.toLocaleString();
    document.getElementById('finalTotal').textContent = Math.max(0, finalTotal).toLocaleString();
    
    // Update discount row visibility
    const discountRow = document.getElementById('discountRow');
    if (discountAmount > 0) {
        discountRow.classList.remove('hidden');
        document.getElementById('discountAmount').textContent = discountAmount.toLocaleString();
    } else {
        discountRow.classList.add('hidden');
    }
    
    // Re-validate coupon if applied
    if (appliedCoupon) {
        validateCoupon(true);
    }
}

function updateDateAvailability() {
    const date = document.getElementById('bookingDate').value;
    if (!date) return;
    
    // Check availability for selected date
    fetch(`<?= url('/api/tour/availability') ?>?tour_id=${tourId}&start_date=${date}&end_date=${date}`)
        .then(response => response.json())
        .then(data => {
            const availability = data[date];
            const availabilityDiv = document.getElementById('dateAvailability');
            
            if (availability) {
                if (availability.status === 'available' && availability.available_slots > 0) {
                    availabilityDiv.innerHTML = `<span class="text-green-600"><i class="fas fa-check-circle"></i> ${availability.available_slots} slots available</span>`;
                    if (availability.price && availability.price !== tourPrice) {
                        currentPrice = parseFloat(availability.price);
                        updateTotal();
                    } else {
                        currentPrice = tourPrice;
                        updateTotal();
                    }
                } else {
                    availabilityDiv.innerHTML = `<span class="text-red-600"><i class="fas fa-times-circle"></i> Not available</span>`;
                }
            } else {
                availabilityDiv.innerHTML = '';
            }
        })
        .catch(error => {
            console.error('Error checking availability:', error);
        });
}

function loadAvailabilityCalendar() {
    const startDate = new Date();
    const endDate = new Date();
    endDate.setMonth(endDate.getMonth() + 3);
    
    fetch(`<?= url('/api/tour/availability') ?>?tour_id=${tourId}&start_date=${startDate.toISOString().split('T')[0]}&end_date=${endDate.toISOString().split('T')[0]}`)
        .then(response => response.json())
        .then(data => {
            availabilityCalendar = data;
            renderCalendar();
        })
        .catch(error => {
            console.error('Error loading calendar:', error);
        });
}

function renderCalendar() {
    const calendarGrid = document.getElementById('calendarGrid');
    if (!calendarGrid) return;
    
    const today = new Date();
    const currentMonth = today.getMonth();
    const currentYear = today.getFullYear();
    
    // Month header
    const monthNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    let html = '';
    monthNames.forEach(day => {
        html += `<div class="font-semibold text-gray-700 text-center py-1">${day}</div>`;
    });
    
    // Get first day of month
    const firstDay = new Date(currentYear, currentMonth, 1).getDay();
    
    // Add empty cells for days before month starts
    for (let i = 0; i < firstDay; i++) {
        html += '<div></div>';
    }
    
    // Add days of month
    const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
    for (let day = 1; day <= daysInMonth; day++) {
        const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const availability = availabilityCalendar[dateStr];
        const isPast = new Date(dateStr) < new Date(today.toISOString().split('T')[0]);
        
        let className = 'p-2 text-center rounded cursor-pointer ';
        let status = '';
        
        if (isPast) {
            className += 'bg-gray-100 text-gray-400 cursor-not-allowed';
        } else if (availability && availability.status === 'available' && availability.available_slots > 0) {
            className += 'bg-green-100 text-green-700 hover:bg-green-200';
            status = availability.available_slots;
        } else {
            className += 'bg-red-100 text-red-700 cursor-not-allowed';
        }
        
        html += `<div class="${className}" ${!isPast && availability && availability.status === 'available' ? `onclick="selectDate('${dateStr}')"` : ''}>
            <div>${day}</div>
            ${status ? `<div class="text-xs">${status}</div>` : ''}
        </div>`;
    }
    
    calendarGrid.innerHTML = html;
}

function selectDate(dateStr) {
    document.getElementById('bookingDate').value = dateStr;
    updateDateAvailability();
}

function validateCoupon(silent = false) {
    const couponCode = document.getElementById('couponCode').value.trim().toUpperCase();
    const couponMessage = document.getElementById('couponMessage');
    
    if (!couponCode) {
        if (!silent) {
            couponMessage.innerHTML = '<span class="text-red-600">Please enter a coupon code</span>';
        }
        return;
    }
    
    const guests = parseInt(document.getElementById('guestsSelect').value);
    const total = currentPrice * guests;
    
    const formData = new FormData();
    formData.append('coupon_code', couponCode);
    formData.append('tour_id', tourId);
    formData.append('amount', total);
    
    fetch('<?= url('/api/coupon/validate') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.valid) {
            appliedCoupon = couponCode;
            discountAmount = parseFloat(data.discount_amount);
            couponMessage.innerHTML = `<span class="text-green-600"><i class="fas fa-check-circle"></i> ${data.message}</span>`;
            updateTotal();
        } else {
            appliedCoupon = null;
            discountAmount = 0;
            couponMessage.innerHTML = `<span class="text-red-600"><i class="fas fa-times-circle"></i> ${data.message}</span>`;
            updateTotal();
        }
    })
    .catch(error => {
        if (!silent) {
            couponMessage.innerHTML = '<span class="text-red-600">Error validating coupon</span>';
        }
    });
}

// Load calendar on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAvailabilityCalendar();
    updateTotal();
});

function bookNow() {
    <?php if (!$isLoggedIn): ?>
        window.location.href = '<?= url('/login') ?>';
    <?php else: ?>
        const form = document.getElementById('bookingForm');
        const formData = new FormData(form);
        const paymentType = formData.get('payment_type') || 'vnpay';
        
        // Enable/disable payment method select based on radio selection
        const paymentMethodSelect = document.getElementById('paymentMethodSelect');
        if (paymentMethodSelect) {
            paymentMethodSelect.disabled = (paymentType !== 'saved');
        }
        
        // Add coupon code if applied
        if (appliedCoupon) {
            formData.append('coupon_code', appliedCoupon);
        }
        
        if (paymentType === 'vnpay') {
            // Create booking first, then redirect to VNPay
            fetch('<?= url('/api/booking') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.booking_id) {
                    // Create VNPay payment URL
                    const paymentFormData = new FormData();
                    paymentFormData.append('booking_id', data.booking_id);
                    
                    fetch('<?= url('/payment/vnpay/create') ?>', {
                        method: 'POST',
                        body: paymentFormData
                    })
                    .then(response => response.json())
                    .then(paymentData => {
                        if (paymentData.success && paymentData.payment_url) {
                            // Redirect to VNPay
                            window.location.href = paymentData.payment_url;
                        } else {
                            alert('Failed to create payment: ' + (paymentData.error || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        alert('Error creating payment: ' + error.message);
                    });
                } else {
                    alert('Failed to create booking: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        } else {
            // Use saved payment method
            fetch('<?= url('/api/booking') ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Booking created successfully!');
                    window.location.href = '<?= url('/dashboard?view=bookings') ?>';
                } else {
                    alert('Failed to create booking: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
            });
        }
    <?php endif; ?>
}

// Enable/disable payment method select based on radio selection
document.addEventListener('DOMContentLoaded', function() {
    const paymentTypeRadios = document.querySelectorAll('input[name="payment_type"]');
    const paymentMethodSelect = document.getElementById('paymentMethodSelect');
    
    if (paymentTypeRadios.length > 0 && paymentMethodSelect) {
        paymentTypeRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                paymentMethodSelect.disabled = (this.value !== 'saved');
            });
        });
    }
});

<?php if ($isLoggedIn): ?>
function toggleWishlist(tourId) {
    fetch('<?= url('/api/wishlist/add') ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'tour_id=' + tourId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('wishlistBtn').innerHTML = '<i class="fas fa-heart text-red-500"></i>';
        } else {
            alert(data.error || 'Failed to add to wishlist');
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
<?php endif; ?>
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
