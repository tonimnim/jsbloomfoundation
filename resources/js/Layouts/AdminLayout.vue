<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AdminSidebar from '@/Components/Admin/AdminSidebar.vue';
import AdminTopBar from '@/Components/Admin/AdminTopBar.vue';

const page = usePage();
const sidebarOpen = ref(false);
const isMobile = ref(false);

// Check if user is admin
const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role === 'admin');

// Handle window resize
const handleResize = () => {
    if (typeof window !== 'undefined') {
        isMobile.value = window.innerWidth < 768;
        if (!isMobile.value) {
            sidebarOpen.value = false;
        }
    }
};

// Toggle sidebar
const toggleSidebar = () => {
    sidebarOpen.value = !sidebarOpen.value;
};

// Setup event listeners on mount
onMounted(() => {
    handleResize(); // Set initial state
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Sidebar -->
        <AdminSidebar 
            :open="sidebarOpen" 
            :is-mobile="isMobile"
            @toggle="toggleSidebar"
        />
        
        <!-- Main Content Area -->
        <div class="lg:pl-64">
            <!-- Top Bar -->
            <AdminTopBar 
                :user="user"
                @toggle-sidebar="toggleSidebar"
            />
            
            <!-- Page Header -->
            <header v-if="$slots.header" class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-4 sm:px-6 lg:px-8 py-4">
                    <slot name="header" />
                </div>
            </header>
            
            <!-- Main Content -->
            <main class="py-6">
                <div class="px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>
        
        <!-- Mobile Sidebar Overlay -->
        <div 
            v-if="sidebarOpen && isMobile"
            class="fixed inset-0 z-20 bg-black bg-opacity-50 lg:hidden"
            @click="toggleSidebar"
        ></div>
    </div>
</template>

<style scoped>
/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
    width: 6px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
}

::-webkit-scrollbar-thumb {
    background: #94a3b8;
    border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
    background: #64748b;
}
</style>