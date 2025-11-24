<?php
$pageTitle = 'Contact Us - TravelQuest';
$showNavbar = true;
$showFooter = true;
ob_start();
?>
<div class="min-h-screen bg-gray-50 py-12 px-4">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Get In Touch</h1>
            <p class="text-lg text-gray-600">Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-envelope text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Email Us</h3>
                <p class="text-gray-600">info@travelquest.com</p>
                <p class="text-gray-600">support@travelquest.com</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-phone text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Call Us</h3>
                <p class="text-gray-600">+1 (555) 123-4567</p>
                <p class="text-gray-600">+1 (555) 987-6543</p>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="far fa-clock text-blue-600 text-2xl"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Business Hours</h3>
                <p class="text-gray-600">Mon - Fri: 9:00 AM - 6:00 PM</p>
                <p class="text-gray-600">Sat - Sun: 10:00 AM - 4:00 PM</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">Send Us a Message</h2>
                <form class="space-y-6" onsubmit="handleSubmit(event)">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Your Name</label>
                        <input type="text" required placeholder="John Doe" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Your Email</label>
                        <input type="email" required placeholder="you@example.com" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Subject</label>
                        <input type="text" required placeholder="How can we help?" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                        <textarea required rows="6" placeholder="Tell us more about your inquiry..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>

                    <button type="submit" class="w-full flex items-center justify-center space-x-2 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-semibold">
                        <i class="fas fa-paper-plane"></i>
                        <span>Send Message</span>
                    </button>
                </form>
            </div>

            <div class="space-y-8">
                <div class="bg-white rounded-xl shadow-lg p-8">
                    <div class="flex items-start space-x-4 mb-6">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-blue-600"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Visit Our Office</h3>
                            <p class="text-gray-600">123 Travel Street</p>
                            <p class="text-gray-600">Adventure City, AC 12345</p>
                            <p class="text-gray-600">United States</p>
                        </div>
                    </div>

                    <div class="w-full h-64 bg-gray-200 rounded-lg overflow-hidden">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d193595.15830869428!2d-74.11976383964465!3d40.69766374874431!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x89c24fa5d33f083b%3A0xc80b8f06e177fe62!2sNew%20York%2C%20NY%2C%20USA!5e0!3m2!1sen!2s!4v1234567890" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>

                <div class="bg-blue-600 rounded-xl shadow-lg p-8 text-white">
                    <h3 class="text-2xl font-bold mb-4">Frequently Asked Questions</h3>
                    <p class="mb-4">Looking for quick answers? Check out our FAQ section where we've answered the most common questions about bookings, payments, and travel requirements.</p>
                    <button class="bg-white text-blue-600 px-6 py-2 rounded-lg hover:bg-gray-100 transition-colors font-semibold">View FAQs</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function handleSubmit(e) {
    e.preventDefault();
    alert('Thank you for your message! We will get back to you soon.');
    e.target.reset();
}
</script>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
