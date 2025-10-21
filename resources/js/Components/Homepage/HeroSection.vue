<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    settings: Object,
});

// Carousel state
const currentImageIndex = ref(0);
const imageInterval = ref(null);
const isLoading = ref(false);
const loadedImages = ref(new Set());
const isPaused = ref(false);
const isTransitioning = ref(false);

// Text animation state
const currentTextIndex = ref(0);
const textInterval = ref(null);
const descriptions = [
    "Empowering the most vulnerable members of our communities through essential resources, education, and care.",
    "Nurturing growth and creating opportunities for children, youth, and the elderly to bloom into their full potential."
];

// JS Bloom hero images - Full background
const heroImages = [
    '/images/hero-1.jpg',
    '/images/hero-2.jpg',
    '/images/hero-3.jpg',
    '/images/hero-4.jpg'
];

// Computed
const hasImages = computed(() => heroImages.length > 0);
const imageCount = computed(() => heroImages.length);

// Preload images
const preloadImages = () => {
    if (!hasImages.value) {
        isLoading.value = false;
        return;
    }

    let loadedCount = 0;
    const totalImages = heroImages.length;

    heroImages.forEach((imageUrl, index) => {
        const img = new Image();

        img.onload = () => {
            loadedCount++;
            loadedImages.value.add(index);
            if (loadedCount === totalImages) {
                isLoading.value = false;
            }
        };
        img.onerror = (error) => {
            loadedCount++;
            if (loadedCount === totalImages) {
                isLoading.value = false;
            }
        };
        img.src = imageUrl;
    });

    // Fallback timeout
    setTimeout(() => {
        if (isLoading.value) {
            isLoading.value = false;
        }
    }, 5000);
};

// Start image carousel
const startImageCarousel = () => {
    if (hasImages.value && imageCount.value > 1 && !isPaused.value) {
        imageInterval.value = setInterval(() => {
            nextImage();
        }, 8000); // 8 seconds per image for Ken Burns effect
    }
};

// Stop carousel
const stopImageCarousel = () => {
    if (imageInterval.value) {
        clearInterval(imageInterval.value);
        imageInterval.value = null;
    }
};

// Next image with transition
const nextImage = () => {
    if (isTransitioning.value) return;

    isTransitioning.value = true;
    currentImageIndex.value = (currentImageIndex.value + 1) % imageCount.value;

    setTimeout(() => {
        isTransitioning.value = false;
    }, 2000); // 2 second crossfade duration
};

// Go to specific image
const goToImage = (index) => {
    if (index === currentImageIndex.value || isTransitioning.value) return;
    
    isTransitioning.value = true;
    setTimeout(() => {
        currentImageIndex.value = index;
        stopImageCarousel();
        setTimeout(() => {
            isTransitioning.value = false;
            startImageCarousel();
        }, 300);
    }, 300);
};

// Start text rotation
const startTextRotation = () => {
    textInterval.value = setInterval(() => {
        currentTextIndex.value = (currentTextIndex.value + 1) % descriptions.length;
    }, 3000);
};

// Stop text rotation
const stopTextRotation = () => {
    if (textInterval.value) {
        clearInterval(textInterval.value);
        textInterval.value = null;
    }
};

// Pause/resume on hover
const pauseCarousel = () => {
    isPaused.value = true;
    stopImageCarousel();
};

const resumeCarousel = () => {
    isPaused.value = false;
    startImageCarousel();
};

onMounted(() => {
    preloadImages();
    startImageCarousel();
    startTextRotation();
});

onUnmounted(() => {
    stopImageCarousel();
    stopTextRotation();
});

// Watch for loading complete
watch(isLoading, (newValue) => {
    if (!newValue) {
        startImageCarousel();
    }
});
</script>

<template>
    <!-- Hero Section -->
    <section class="relative min-h-screen overflow-hidden">

        <!-- Full Background Image Carousel with Ken Burns Effect -->
        <div class="absolute inset-0">
            <div
                v-for="(image, index) in heroImages"
                :key="index"
                class="absolute inset-0 transition-opacity duration-[2000ms] ease-in-out"
                :class="{
                    'opacity-100': index === currentImageIndex,
                    'opacity-0': index !== currentImageIndex
                }"
            >
                <img
                    :src="image"
                    :alt="`JS Bloom Foundation - Image ${index + 1}`"
                    class="w-full h-full object-cover ken-burns-effect"
                    :class="{ 'animate-ken-burns': index === currentImageIndex }"
                />
            </div>
            <!-- Dark overlay for text readability -->
            <div class="absolute inset-0 bg-black/40"></div>
        </div>

        <!-- Content -->
        <div class="relative z-10 w-full flex items-center justify-center min-h-screen py-20">
            <div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl mx-auto text-center space-y-8">
                    <!-- Main Heading -->
                    <div class="space-y-4">
                        <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold leading-tight text-white">
                            <span class="inline">Inspire </span>
                            <span class="inline text-transparent bg-clip-text bg-gradient-to-r from-bloom-yellow-400 to-bloom-yellow-500">
                                Lasting
                            </span>
                            <span class="inline text-transparent bg-clip-text bg-gradient-to-r from-bloom-blue-400 to-bloom-blue-500">
                                Change
                            </span>
                        </h1>

                        <!-- Animated Description -->
                        <div class="relative h-24 md:h-28 overflow-hidden">
                            <div
                                v-for="(text, index) in descriptions"
                                :key="index"
                                class="absolute inset-0 flex items-center justify-center transition-all duration-1000 ease-in-out"
                                :class="{
                                    'opacity-100 transform translate-y-0': index === currentTextIndex,
                                    'opacity-0 transform -translate-y-full': index < currentTextIndex || (currentTextIndex === 0 && index === descriptions.length - 1),
                                    'opacity-0 transform translate-y-full': index > currentTextIndex && !(currentTextIndex === descriptions.length - 1 && index === 0)
                                }"
                            >
                                <p class="text-lg md:text-xl text-white leading-relaxed">
                                    {{ text }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Call-to-Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 pt-6 justify-center">
                        <a
                            href="https://www.gofundme.com/f/2nd-annual-help-young-adults-gain-daily-essentials?lid=8bgjo94uhpsd&utm_source=product&utm_medium=email&utm_campaign=TXN_donation_alert&utm_content=fundraiser_page"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="group relative px-8 py-3.5 bg-gradient-to-r from-bloom-yellow-500 to-bloom-yellow-600 text-white rounded-full font-semibold text-base shadow-xl hover:shadow-bloom-yellow-500/50 transform hover:-translate-y-0.5 hover:scale-105 transition-all duration-300 inline-block text-center"
                        >
                            <span class="relative z-10">Donate Now</span>
                        </a>
                        <a
                            href="#about"
                            class="group px-8 py-3.5 border-2 border-white text-white rounded-full font-semibold text-base bg-white/10 backdrop-blur-sm hover:bg-white hover:text-bloom-blue-600 transition-all duration-300 inline-block text-center"
                        >
                            Learn Our Story
                            <svg class="inline-block ml-1.5 w-4 h-4 transform group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    </div>

                    <!-- Image Navigation Dots -->
                    <div class="flex items-center justify-center space-x-3 pt-8">
                        <button
                            v-for="(image, index) in heroImages"
                            :key="index"
                            @click="goToImage(index)"
                            class="group relative"
                        >
                            <div
                                :class="[
                                    'transition-all duration-500',
                                    index === currentImageIndex
                                        ? 'w-12 h-2 bg-bloom-yellow-500 rounded-full shadow-lg'
                                        : 'w-2 h-2 bg-white/70 hover:bg-white rounded-full'
                                ]"
                            ></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-12 left-1/2 transform -translate-x-1/2 text-white animate-bounce z-20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </section>
</template>

<style scoped>
/* Base responsive adjustments */
section {
    min-height: 100vh;
    min-height: 100dvh;
}

/* Mobile specific adjustments */
@media (max-width: 640px) {
    section {
        min-height: 100vh;
        min-height: 100dvh;
    }
}

/* Landscape mobile adjustments */
@media (max-height: 500px) and (orientation: landscape) {
    section {
        min-height: auto;
    }
}

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.25, 0.1, 0.25, 1);
}

/* Prevent layout shift during loading */
img {
    will-change: transform;
}

/* Ken Burns Effect - Slow Zoom and Pan */
.ken-burns-effect {
    transition: transform 8s ease-in-out;
}

.animate-ken-burns {
    animation: kenBurns 8s ease-in-out infinite alternate;
}

@keyframes kenBurns {
    0% {
        transform: scale(1) translate(0, 0);
    }
    100% {
        transform: scale(1.1) translate(-2%, -2%);
    }
}

/* Animation for bounce */
@keyframes bounce {
    0%, 100% {
        transform: translateX(-50%) translateY(0);
    }
    50% {
        transform: translateX(-50%) translateY(-25%);
    }
}

.animate-bounce {
    animation: bounce 2s infinite;
}

/* Text slide animation */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-100%);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideUp {
    from {
        opacity: 1;
        transform: translateY(0);
    }
    to {
        opacity: 0;
        transform: translateY(-100%);
    }
}
</style>