<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    settings: Object,
});

// Animation state
const isPaused = ref(false);
const carouselPosition = ref(0);
const animationId = ref(null);

// Success stories images
const successStories = [
    { id: 1, image: "/images/2.jpg" },
    { id: 2, image: "/images/3.jpg" },
    { id: 3, image: "/images/4.jpg" },
    { id: 4, image: "/images/5.jpg" },
    { id: 5, image: "/images/8.jpg" },
];

// Double the stories for seamless loop
const stories = ref([...successStories, ...successStories]);

// Animation function
const animate = () => {
    if (!isPaused.value) {
        carouselPosition.value -= 0.5; // Slower movement
        
        // Reset when first set is scrolled out
        const cardWidth = 400; // Approximate width of each card
        const totalWidth = successStories.length * cardWidth;
        if (Math.abs(carouselPosition.value) >= totalWidth) {
            carouselPosition.value = 0;
        }
    }
    animationId.value = requestAnimationFrame(animate);
};

// Start/stop animation
const startAnimation = () => {
    animate();
};

const stopAnimation = () => {
    if (animationId.value) {
        cancelAnimationFrame(animationId.value);
    }
};

// Pause/resume carousel
const pauseCarousel = () => {
    isPaused.value = true;
};

const resumeCarousel = () => {
    isPaused.value = false;
};

onMounted(() => {
    startAnimation();
});

onUnmounted(() => {
    stopAnimation();
});
</script>

<template>
    <section class="py-8 bg-gradient-to-b from-white to-gray-50 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
            <!-- Section Header -->
            <div class="text-center">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                    Success Stories
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Empowering children, youth, and the elderly to bloom into their full potential.
                </p>
            </div>
        </div>

        <!-- Scrolling Carousel -->
        <div 
            class="relative"
            @mouseenter="pauseCarousel"
            @mouseleave="resumeCarousel"
        >
            <!-- Gradient overlays -->
            <div class="absolute left-0 top-0 bottom-0 w-32 bg-gradient-to-r from-white via-white/50 to-transparent z-10"></div>
            <div class="absolute right-0 top-0 bottom-0 w-32 bg-gradient-to-l from-white via-white/50 to-transparent z-10"></div>
            
            <!-- Stories Container -->
            <div class="flex transition-transform duration-300 ease-linear"
                 :style="{ transform: `translateX(${carouselPosition}px)` }">
                <div
                    v-for="(story, index) in stories"
                    :key="`${story.id}-${index}`"
                    class="flex-shrink-0 w-96 mx-4"
                >
                    <div class="rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-shadow duration-300">
                        <!-- Image -->
                        <div class="h-96 overflow-hidden">
                            <img
                                :src="story.image"
                                alt="Success Story"
                                class="w-full h-full object-cover hover:scale-105 transition-transform duration-700"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Impact Summary -->
        <div class="max-w-4xl mx-auto mt-16 px-4 sm:px-6 lg:px-8">
            <div class="bg-bloom-blue-600 rounded-2xl p-8 text-white text-center shadow-xl">
                <h3 class="text-2xl font-bold mb-4">Together, We're Making a Difference</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                    <div>
                        <p class="text-3xl font-bold">1,250+</p>
                        <p class="text-sm">Lives Transformed</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold">45+</p>
                        <p class="text-sm">Projects Completed</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold">12</p>
                        <p class="text-sm">Counties Reached</p>
                    </div>
                    <div>
                        <p class="text-3xl font-bold">100%</p>
                        <p class="text-sm">Commitment</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>