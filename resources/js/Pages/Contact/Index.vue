<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    settings: Object,
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success);

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    subject: '',
    message: '',
});

const subjects = [
    'General Inquiry',
    'Partnership Opportunities',
    'Volunteer Information',
    'Donation Inquiry',
    'Media Request',
    'Other'
];

const submit = () => {
    form.post(route('contact.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Contact Us - JS Bloom Foundation" />
    
    <PublicLayout :settings="settings">
        <!-- Hero Section -->
        <section class="bg-gradient-to-r from-blue-600 to-teal-600 text-white py-20 mt-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h1 class="text-4xl lg:text-5xl font-bold text-center mb-6">Contact Us</h1>
                <p class="text-xl text-center max-w-3xl mx-auto">
                    We'd love to hear from you. Whether you have questions about our programs, 
                    want to partner with us, or are interested in volunteering, we're here to help.
                </p>
            </div>
        </section>

        <!-- Contact Section -->
        <section class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="successMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-8 rounded-lg">
                    <p class="font-medium">{{ successMessage }}</p>
                </div>
                <div class="grid lg:grid-cols-2 gap-12">
                    <!-- Contact Information -->
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Get in Touch</h2>
                        <p class="text-gray-600 mb-8">
                            JS Bloom Foundation is dedicated to empowering the most vulnerable members of our
                            communities—children, youth, and the elderly—by providing essential resources, education,
                            and care. We help them bloom into their full potential.
                        </p>

                        <div class="space-y-6">
                            <!-- Office Location -->
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Office Location</h3>
                                    <p class="text-gray-600">{{ settings.physical_address || 'Lagos, Nigeria' }}</p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Email Address</h3>
                                    <a :href="`mailto:${settings.contact_email}`" class="text-blue-600 hover:text-blue-800">
                                        {{ settings.contact_email || 'info@jsbloomfoundation.org' }}
                                    </a>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Phone Number</h3>
                                    <a :href="`tel:${settings.contact_phone}`" class="text-blue-600 hover:text-blue-800">
                                        {{ settings.contact_phone || '+234 800 000 0000' }}
                                    </a>
                                </div>
                            </div>

                            <!-- Social Media -->
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Follow Us</h3>
                                    <div class="flex space-x-3 mt-2">
                                        <a href="#" class="w-8 h-8 bg-gray-200 hover:bg-blue-600 hover:text-white rounded-full flex items-center justify-center transition-colors duration-300">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                            </svg>
                                        </a>
                                        <a href="#" class="w-8 h-8 bg-gray-200 hover:bg-blue-600 hover:text-white rounded-full flex items-center justify-center transition-colors duration-300">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                            </svg>
                                        </a>
                                        <a href="#" class="w-8 h-8 bg-gray-200 hover:bg-blue-600 hover:text-white rounded-full flex items-center justify-center transition-colors duration-300">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zM5.838 12a6.162 6.162 0 1112.324 0 6.162 6.162 0 01-12.324 0zM12 16a4 4 0 110-8 4 4 0 010 8zm4.965-10.405a1.44 1.44 0 112.881.001 1.44 1.44 0 01-2.881-.001z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Form -->
                    <div class="bg-white rounded-2xl shadow-xl p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Send Us a Message</h2>
                        
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- First Name -->
                                <div>
                                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        First name*
                                    </label>
                                    <input
                                        id="first_name"
                                        v-model="form.first_name"
                                        type="text"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-300"
                                        :class="{ 'border-red-500': form.errors.first_name }"
                                    />
                                    <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.first_name }}
                                    </p>
                                </div>

                                <!-- Last Name -->
                                <div>
                                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                        Last name*
                                    </label>
                                    <input
                                        id="last_name"
                                        v-model="form.last_name"
                                        type="text"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-300"
                                        :class="{ 'border-red-500': form.errors.last_name }"
                                    />
                                    <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.last_name }}
                                    </p>
                                </div>
                            </div>

                            <!-- Email -->
                            <div class="mt-6">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email*
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    placeholder="you@example.com"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-300"
                                    :class="{ 'border-red-500': form.errors.email }"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.email }}
                                </p>
                            </div>

                            <!-- Phone -->
                            <div class="mt-6">
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    placeholder="+234 800 000 0000"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-300"
                                    :class="{ 'border-red-500': form.errors.phone }"
                                />
                                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                            <!-- Subject -->
                            <div class="mt-6">
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                    How can we help you?*
                                </label>
                                <select
                                    id="subject"
                                    v-model="form.subject"
                                    required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-300"
                                    :class="{ 'border-red-500': form.errors.subject }"
                                >
                                    <option value="">Please Select</option>
                                    <option v-for="subj in subjects" :key="subj" :value="subj">
                                        {{ subj }}
                                    </option>
                                </select>
                                <p v-if="form.errors.subject" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.subject }}
                                </p>
                            </div>

                            <!-- Message -->
                            <div class="mt-6">
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                    Message*
                                </label>
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    rows="4"
                                    required
                                    placeholder="Tell us how we can help..."
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-300 resize-none"
                                    :class="{ 'border-red-500': form.errors.message }"
                                ></textarea>
                                <p v-if="form.errors.message" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.message }}
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-8">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="w-full md:w-auto px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="!form.processing">Submit</span>
                                    <span v-else>Sending...</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>