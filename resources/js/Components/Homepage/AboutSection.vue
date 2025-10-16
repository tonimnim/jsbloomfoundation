<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    settings: Object,
});

// Animation states
const isVisible = ref(false);

// Intersection observer for animations
const observerCallback = (entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            isVisible.value = true;
        }
    });
};

let observer = null;

onMounted(() => {
    observer = new IntersectionObserver(observerCallback, {
        threshold: 0.1
    });
    
    const section = document.querySelector('#about');
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
    <section id="about" class="relative overflow-hidden bg-gradient-to-br from-bloom-blue-50 via-white to-bloom-yellow-50">
        <div class="relative py-16 sm:py-20 lg:py-24">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- Section Header -->
                <div class="text-center mb-12 sm:mb-16" :class="{ 'opacity-0 translate-y-10': !isVisible, 'opacity-100 translate-y-0 transition-all duration-1000': isVisible }">
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                        About
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-bloom-yellow-500 to-bloom-yellow-600">
                            JS Bloom
                        </span>
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-bloom-blue-500 to-bloom-blue-600">
                            Foundation
                        </span>
                    </h2>
                </div>

                <!-- Mission Statement -->
                <div class="mb-12 sm:mb-16" :class="{ 'opacity-0 translate-y-10': !isVisible, 'opacity-100 translate-y-0 transition-all duration-1000 delay-200': isVisible }">
                    <div class="bg-white rounded-2xl p-8 sm:p-10 shadow-lg border-t-4 border-bloom-yellow-500">
                        <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-8 h-8 text-bloom-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Our Mission
                        </h3>
                        <p class="text-base sm:text-lg text-gray-700 leading-relaxed">
                            At JS Bloom, we are dedicated to empowering the most vulnerable members of our communities—children, youth, and the elderly—by providing essential resources, education, and care. Our mission is to nurture growth and create opportunities for the less privileged, helping them bloom into their full potential by addressing their immediate needs and building a sustainable foundation for their future.
                        </p>
                    </div>
                </div>

                <!-- Aims and Objectives -->
                <div :class="{ 'opacity-0 translate-y-10': !isVisible, 'opacity-100 translate-y-0 transition-all duration-1000 delay-400': isVisible }">
                    <h3 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-8 text-center">
                        Our Aims & Objectives
                    </h3>

                    <div class="space-y-6">
                        <!-- Objective 1 -->
                        <div class="bg-white rounded-xl p-6 sm:p-8 shadow-md hover:shadow-xl transition-shadow duration-300 border-l-4 border-bloom-blue-500">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-bloom-blue-500 to-bloom-blue-600 rounded-lg flex items-center justify-center mr-4">
                                    <span class="text-white text-xl font-bold">1</span>
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">Empower Vulnerable Children</h4>
                                    <p class="text-gray-700 leading-relaxed">
                                        To support young girls and boys by providing them with the educational tools, health resources, and confidence needed to thrive.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Objective 2 -->
                        <div class="bg-white rounded-xl p-6 sm:p-8 shadow-md hover:shadow-xl transition-shadow duration-300 border-l-4 border-bloom-yellow-500">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-bloom-yellow-500 to-bloom-yellow-600 rounded-lg flex items-center justify-center mr-4">
                                    <span class="text-white text-xl font-bold">2</span>
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">Promote Health and Well-being</h4>
                                    <p class="text-gray-700 leading-relaxed">
                                        To enhance the well-being of women and girls through menstrual health education and access to hygiene products, while also supporting the elderly with essential care and resources.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Objective 3 -->
                        <div class="bg-white rounded-xl p-6 sm:p-8 shadow-md hover:shadow-xl transition-shadow duration-300 border-l-4 border-bloom-blue-500">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-bloom-blue-500 to-bloom-blue-600 rounded-lg flex items-center justify-center mr-4">
                                    <span class="text-white text-xl font-bold">3</span>
                                </div>
                                <div>
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">Alleviate Water Scarcity</h4>
                                    <p class="text-gray-700 leading-relaxed">
                                        To build sustainable water solutions, such as boreholes and wells, for communities facing chronic water shortages.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</template>