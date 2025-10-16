<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    settings: Object,
    images: {
        type: Array,
        default: () => []
    }
});

const selectedImage = ref(null);
const currentImageIndex = ref(-1);

const openLightbox = (image) => {
    selectedImage.value = image;
    currentImageIndex.value = props.images.findIndex(img => img.id === image.id);
};

const closeLightbox = () => {
    selectedImage.value = null;
    currentImageIndex.value = -1;
};

const nextImage = () => {
    if (currentImageIndex.value < props.images.length - 1) {
        currentImageIndex.value++;
        selectedImage.value = props.images[currentImageIndex.value];
    }
};

const previousImage = () => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
        selectedImage.value = props.images[currentImageIndex.value];
    }
};

// Handle keyboard navigation
const handleKeyboard = (event) => {
    if (selectedImage.value) {
        if (event.key === 'Escape') {
            closeLightbox();
        } else if (event.key === 'ArrowRight') {
            nextImage();
        } else if (event.key === 'ArrowLeft') {
            previousImage();
        }
    }
};

// Add keyboard event listener when component is mounted
import { onMounted, onUnmounted } from 'vue';

onMounted(() => {
    document.addEventListener('keydown', handleKeyboard);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeyboard);
});
</script>

<template>
    <Head title="Gallery - JS Bloom Foundation" />

    <PublicLayout :settings="settings">
        <!-- Hero Section -->
        <section class="relative py-32 sm:py-40 lg:py-48 overflow-hidden">
            <!-- Background Image -->
            <div class="absolute inset-0">
                <img 
                    src="/images/gallerybg.jpg" 
                    alt="Gallery Background" 
                    class="w-full h-full object-cover"
                >
                <!-- Lighter Overlay for better text visibility while showing image -->
                <div class="absolute inset-0 bg-gradient-to-b from-black/40 to-black/60"></div>
            </div>
            
            <!-- Content -->
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4 drop-shadow-2xl">
                        Our Gallery
                    </h1>
                    <p class="text-lg sm:text-xl text-white max-w-3xl mx-auto drop-shadow-lg">
                        Explore moments captured from our events, programs, and community activities
                    </p>
                </div>
            </div>
        </section>

        <!-- Gallery Content -->
        <section class="py-16 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Gallery Grid -->
                <div v-if="images && images.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-4">
                    <div 
                        v-for="image in images" 
                        :key="image.id"
                        @click="openLightbox(image)"
                        class="group relative aspect-square overflow-hidden rounded-lg bg-gray-200 cursor-pointer hover:opacity-90 transition-opacity"
                    >
                        <img 
                            :src="image.url"
                            :alt="`Gallery image`"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        />
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-300"></div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-20">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-200 rounded-full mb-4">
                        <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h2 class="text-2xl font-semibold text-gray-900 mb-2">No Images Yet</h2>
                    <p class="text-gray-600">Check back soon for our photo gallery!</p>
                </div>
            </div>
        </section>

        <!-- Lightbox Modal -->
        <div v-if="selectedImage" class="fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4" @click="closeLightbox">
            <div class="relative max-w-6xl max-h-[90vh]" @click.stop>
                <img 
                    :src="selectedImage.url"
                    :alt="`Gallery image`"
                    class="max-w-full max-h-[90vh] object-contain"
                />
                
                <!-- Close Button -->
                <button 
                    @click="closeLightbox"
                    class="absolute top-4 right-4 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-2"
                >
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- Navigation Arrows -->
                <button 
                    v-if="currentImageIndex > 0"
                    @click.stop="previousImage"
                    class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-2"
                >
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <button 
                    v-if="currentImageIndex < images.length - 1"
                    @click.stop="nextImage"
                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-2"
                >
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </PublicLayout>
</template>