<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import HeroSection from '@/Components/Homepage/HeroSection.vue';
import AboutSection from '@/Components/Homepage/AboutSection.vue';
import ImpactSection from '@/Components/Homepage/ImpactSection.vue';
import TestimonialsSection from '@/Components/Homepage/TestimonialsSection.vue';
import NewsSection from '@/Components/Homepage/NewsSection.vue';
import GetInvolvedSection from '@/Components/Homepage/GetInvolvedSection.vue';

const props = defineProps({
    settings: Object,
    latestPosts: {
        type: Array,
        default: () => []
    }
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success);
</script>

<template>
    <Head title="Home - JS Bloom Foundation" />
    
    <PublicLayout :settings="settings">
        <!-- Success Message Banner -->
        <div v-if="successMessage" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
            <p class="font-medium">{{ successMessage }}</p>
        </div>
        
        <!-- Hero Section with Image Slider -->
        <HeroSection :settings="settings" />
        
        <!-- About Us Section -->
        <AboutSection :settings="settings" />
        
        <!-- Our Impact Section -->
        <ImpactSection :settings="settings" />

        <!-- Success Stories/Testimonials -->
        <TestimonialsSection :settings="settings" />
        
        <!-- Latest News/Updates -->
        <NewsSection :settings="settings" :latest-posts="latestPosts" />

        <!-- Get Involved Section -->
        <GetInvolvedSection :settings="settings" />
    </PublicLayout>
</template>