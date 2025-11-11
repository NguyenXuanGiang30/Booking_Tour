<?php
$pageTitle = 'About Us - TravelQuest';
$showNavbar = true;
$showFooter = true;
ob_start();
?>
<div class="min-h-screen bg-gray-50">
    <section class="relative h-96 bg-cover bg-center" style="background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url(https://images.pexels.com/photos/2662116/pexels-photo-2662116.jpeg);">
        <div class="absolute inset-0 flex items-center justify-center">
            <div class="text-center text-white px-4">
                <h1 class="text-5xl font-bold mb-4">About TravelQuest</h1>
                <p class="text-xl">Creating unforgettable travel experiences since 2015</p>
            </div>
        </div>
    </section>

    <section class="py-16 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg p-8 mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-6">Our Story</h2>
                <div class="space-y-4 text-gray-600 leading-relaxed">
                    <p>Founded in 2015, TravelQuest was born from a simple belief: travel should be accessible, memorable, and transformative. What started as a small team of passionate travelers has grown into a global community of adventure seekers and culture enthusiasts.</p>
                    <p>We've helped over 50,000 travelers discover the world's most incredible destinations. From the beaches of Bali to the peaks of the Swiss Alps, from the temples of Tokyo to the northern lights of Iceland, we curate experiences that go beyond the ordinary.</p>
                    <p>Our mission is to connect people with places and cultures in meaningful ways. We believe that travel has the power to broaden horizons, create understanding, and forge lasting memories. Every tour we offer is designed with care, led by expert guides, and crafted to ensure you return home with stories worth telling.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-900 mb-2">50,000+</h3>
                    <p class="text-gray-600">Happy Travelers</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-globe text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-900 mb-2">120+</h3>
                    <p class="text-gray-600">Destinations</p>
                </div>

                <div class="bg-white rounded-xl shadow-lg p-6 text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-award text-blue-600 text-2xl"></i>
                    </div>
                    <h3 class="text-4xl font-bold text-gray-900 mb-2">15+</h3>
                    <p class="text-gray-600">Industry Awards</p>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-lg p-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">Why Choose Us</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-shield-alt text-blue-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Safe & Secure</h3>
                            <p class="text-gray-600">Your safety is our priority. All tours are fully insured with 24/7 support available.</p>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-award text-blue-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Expert Guides</h3>
                            <p class="text-gray-600">Our local guides are passionate experts who bring destinations to life with their knowledge.</p>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-heart text-blue-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Personalized Service</h3>
                            <p class="text-gray-600">Small group sizes and customizable itineraries ensure a personal touch on every trip.</p>
                        </div>
                    </div>

                    <div class="flex space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-map-marker-alt text-blue-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 mb-2">Best Value</h3>
                            <p class="text-gray-600">Quality accommodations, meals, and experiences at competitive prices with no hidden fees.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-16 px-4 bg-blue-600 text-white">
        <div class="max-w-4xl mx-auto text-center">
            <h2 class="text-4xl font-bold mb-6">Ready to Start Your Adventure?</h2>
            <p class="text-xl mb-8">Join thousands of satisfied travelers and discover the world with TravelQuest</p>
            <a href="<?= url('/tours') ?>" class="bg-white text-blue-600 px-8 py-4 rounded-lg hover:bg-gray-100 transition-colors font-semibold text-lg">Explore Our Tours</a>
        </div>
    </section>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
