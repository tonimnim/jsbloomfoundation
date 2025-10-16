<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    email: String
});
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <Head title="Unsubscribed Successfully" />
        
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-green-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        Unsubscribed Successfully
                    </h2>
                    
                    <p class="text-gray-600 mb-6">
                        You have been successfully unsubscribed from our newsletter. 
                        <span v-if="email" class="block mt-2 font-medium">{{ email }}</span>
                    </p>
                    
                    <p class="text-sm text-gray-500 mb-6">
                        We're sorry to see you go. You won't receive any more newsletters from us.
                    </p>
                    
                    <div class="space-y-3">
                        <Link
                            href="/"
                            class="block w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center transition-colors"
                        >
                            Return to Homepage
                        </Link>
                        
                        <p class="text-xs text-gray-500">
                            Changed your mind? You can always subscribe again from our homepage.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>