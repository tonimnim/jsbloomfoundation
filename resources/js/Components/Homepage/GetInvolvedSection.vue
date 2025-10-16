<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});
</script>

<template>
    <section id="contact" class="py-12 sm:py-14 lg:py-16 bg-gradient-to-b from-white via-bloom-yellow-50 to-bloom-blue-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-8 sm:mb-10">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900">Get Involved</h2>
            </div>

            <!-- Involvement Options Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">

                <!-- Volunteer -->
                <div class="group bg-white rounded-2xl shadow-lg p-6 sm:p-8 hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    <div class="flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-12 h-12 sm:w-14 sm:h-14 text-bloom-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Volunteer</h3>
                    <p class="text-gray-600 mb-6 text-center text-sm sm:text-base">Join our team of dedicated volunteers making a difference in communities worldwide</p>
                    <Link href="/contact" class="block text-center text-bloom-blue-600 font-semibold hover:text-bloom-blue-700 transition-colors duration-300">
                        Sign Up to Volunteer →
                    </Link>
                </div>

                <!-- Donate -->
                <div class="group bg-white rounded-2xl shadow-lg p-6 sm:p-8 hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    <div class="flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-12 h-12 sm:w-14 sm:h-14 text-bloom-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Donate</h3>
                    <p class="text-gray-600 mb-6 text-center text-sm sm:text-base">Your financial support helps us expand our programs and reach more beneficiaries</p>
                    <a href="https://www.gofundme.com/f/young-men-and-women-gain-daily-necessary-essentials" target="_blank" rel="noopener noreferrer" class="block text-center text-bloom-yellow-600 font-semibold hover:text-bloom-yellow-700 transition-colors duration-300">
                        Make a Donation →
                    </a>
                </div>

                <!-- Partner -->
                <div class="group bg-white rounded-2xl shadow-lg p-6 sm:p-8 hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    <div class="flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-12 h-12 sm:w-14 sm:h-14 text-bloom-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Partner</h3>
                    <p class="text-gray-600 mb-6 text-center text-sm sm:text-base">Collaborate with us to create sustainable solutions for community development</p>
                    <Link href="/contact" class="block text-center text-bloom-blue-600 font-semibold hover:text-bloom-blue-700 transition-colors duration-300">
                        Become a Partner →
                    </Link>
                </div>

                <!-- Advocate -->
                <div class="group bg-white rounded-2xl shadow-lg p-6 sm:p-8 hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                    <div class="flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-12 h-12 sm:w-14 sm:h-14 text-bloom-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 text-center">Advocate</h3>
                    <p class="text-gray-600 mb-6 text-center text-sm sm:text-base">Help spread our message and advocate for positive change in your community</p>
                    <Link href="/contact" class="block text-center text-bloom-yellow-600 font-semibold hover:text-bloom-yellow-700 transition-colors duration-300">
                        Join Our Cause →
                    </Link>
                </div>

            </div>

            <!-- Help Us Make a Difference CTA -->
            <div class="mt-12 sm:mt-16 text-center">
                <div class="bg-gradient-to-br from-bloom-blue-500 via-bloom-blue-400 to-bloom-yellow-400 rounded-2xl p-8 sm:p-10 lg:p-12 shadow-xl">
                    <h3 class="text-2xl sm:text-3xl font-bold text-white mb-4">Help Us Make a Difference</h3>
                    <p class="text-base sm:text-lg text-white/90 mb-8 max-w-2xl mx-auto">
                        Your support helps us empower vulnerable communities and create lasting positive change
                    </p>
                    <a
                        href="https://www.gofundme.com/f/young-men-and-women-gain-daily-necessary-essentials"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-block bg-bloom-yellow-500 hover:bg-bloom-yellow-600 text-white px-8 py-4 rounded-full font-semibold text-lg shadow-xl hover:shadow-2xl transform hover:-translate-y-1 transition-all duration-300"
                    >
                        Donate Today
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>