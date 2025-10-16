<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <Head title="Unsubscribe Error" />
        
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-red-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    
                    <h2 class="text-2xl font-bold text-gray-900 mb-4">
                        Unsubscribe Error
                    </h2>
                    
                    <p class="text-gray-600 mb-6">
                        We couldn't process your unsubscribe request. The link may be invalid or has already been used.
                    </p>
                    
                    <div class="space-y-3">
                        <Link
                            href="/"
                            class="block w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center transition-colors"
                        >
                            Return to Homepage
                        </Link>
                        
                        <Link
                            href="/contact"
                            class="block w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-center transition-colors"
                        >
                            Contact Support
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>