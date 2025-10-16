<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const currentYear = computed(() => new Date().getFullYear());
</script>

<template>
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50">
        <!-- Content container -->
        <div class="w-full max-w-md px-6">
            <!-- Logo/Title -->
            <div class="text-center mb-8">
                <Link href="/" class="inline-block">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">JS Bloom Foundation</h1>
                    <p class="text-gray-600 text-sm">Empowering Children, Youth, and the Elderly</p>
                </Link>
            </div>

            <!-- Card -->
            <div class="bg-white shadow-xl rounded-2xl px-8 py-10">
                <slot />
            </div>
            
            <!-- Footer text -->
            <div class="text-center mt-6">
                <p class="text-gray-500 text-sm">
                    © {{ currentYear }} JS Bloom Foundation. All rights reserved.
                </p>
            </div>
        </div>
    </div>
</template>
