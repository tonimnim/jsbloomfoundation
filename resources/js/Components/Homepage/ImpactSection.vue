<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    settings: Object,
});

// Animation state
const isVisible = ref(false);

// Intersection observer for triggering animation
const observerCallback = (entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting && !isVisible.value) {
            isVisible.value = true;
        }
    });
};

let observer = null;

onMounted(() => {
    observer = new IntersectionObserver(observerCallback, {
        threshold: 0.1,
        rootMargin: '50px'
    });

    const section = document.querySelector('#impact');
    if (section) {
        observer.observe(section);
    }
});

onUnmounted(() => {
    if (observer) {
        observer.disconnect();
    }
});
</script>

<template>
    <section id="impact" class="relative py-16 sm:py-20 lg:py-24 bg-gradient-to-br from-bloom-blue-500 via-bloom-blue-400 to-bloom-yellow-400 overflow-hidden">
        <!-- Decorative elements -->
        <div class="hidden sm:block absolute top-0 left-0 w-72 h-72 lg:w-96 lg:h-96 bg-white/10 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
        <div class="hidden sm:block absolute bottom-0 right-0 w-72 h-72 lg:w-96 lg:h-96 bg-white/10 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-12 sm:mb-16" :class="{ 'opacity-0 -translate-y-10': !isVisible, 'opacity-100 translate-y-0 transition-all duration-1000': isVisible }">
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4">
                    Where We Make <span class="text-bloom-yellow-300">Impact</span>
                </h2>
                <p class="text-base sm:text-lg lg:text-xl text-white/90 max-w-3xl mx-auto">
                    Creating meaningful change in the lives of children, youth, and the elderly
                </p>
            </div>

            <!-- Impact Areas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8 mb-12 sm:mb-16">
                <!-- Children Empowerment -->
                <div class="group" :class="{ 'opacity-0 translate-y-10': !isVisible, 'opacity-100 translate-y-0 transition-all duration-1000': isVisible }">
                    <div class="bg-white rounded-2xl p-6 sm:p-8 h-full shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                        <div class="flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-12 h-12 sm:w-14 sm:h-14 text-bloom-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 text-center">Children Empowerment</h3>
                        <p class="text-sm sm:text-base text-gray-600 leading-relaxed text-center">
                            Providing young girls and boys with educational tools, health resources, and confidence needed to thrive and reach their full potential.
                        </p>
                    </div>
                </div>

                <!-- Health & Hygiene -->
                <div class="group" :class="{ 'opacity-0 translate-y-10': !isVisible, 'opacity-100 translate-y-0 transition-all duration-1000 delay-100': isVisible }">
                    <div class="bg-white rounded-2xl p-6 sm:p-8 h-full shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                        <div class="flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-12 h-12 sm:w-14 sm:h-14 text-bloom-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 text-center">Health & Hygiene</h3>
                        <p class="text-sm sm:text-base text-gray-600 leading-relaxed text-center">
                            Enhancing the well-being of women, girls, and the elderly through menstrual health education, hygiene products, and essential care resources.
                        </p>
                    </div>
                </div>

                <!-- Water Solutions -->
                <div class="group" :class="{ 'opacity-0 translate-y-10': !isVisible, 'opacity-100 translate-y-0 transition-all duration-1000 delay-200': isVisible }">
                    <div class="bg-white rounded-2xl p-6 sm:p-8 h-full shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-2">
                        <div class="flex items-center justify-center mx-auto mb-6 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-12 h-12 sm:w-14 sm:h-14 text-bloom-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" />
                            </svg>
                        </div>
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-4 text-center">Water Solutions</h3>
                        <p class="text-sm sm:text-base text-gray-600 leading-relaxed text-center">
                            Building sustainable water infrastructure like boreholes and wells for communities facing chronic water shortages and limited access.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>