<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const props = defineProps({
    user: Object
});

const emit = defineEmits(['toggle-sidebar']);

const showNotifications = ref(false);
const showUserMenu = ref(false);

// Real notifications from database
const notifications = ref([]);
const unreadCount = ref(0);
const isLoadingNotifications = ref(false);

// Fetch notifications from the server
const fetchNotifications = async () => {
    isLoadingNotifications.value = true;
    try {
        const response = await axios.get('/admin/notifications');
        notifications.value = response.data.notifications || [];
        unreadCount.value = response.data.unread_count || 0;
    } catch (error) {
        console.error('Failed to fetch notifications:', error);
        notifications.value = [];
        unreadCount.value = 0;
    } finally {
        isLoadingNotifications.value = false;
    }
};

// Mark notification as read
const markAsRead = async (notification) => {
    if (!notification.unread) return;
    
    try {
        await axios.post(`/admin/notifications/${notification.id}/read`);
        notification.unread = false;
        unreadCount.value = Math.max(0, unreadCount.value - 1);
    } catch (error) {
        console.error('Failed to mark notification as read:', error);
    }
};

// Mark all notifications as read
const markAllAsRead = async () => {
    try {
        await axios.post('/admin/notifications/read-all');
        notifications.value.forEach(n => n.unread = false);
        unreadCount.value = 0;
    } catch (error) {
        console.error('Failed to mark all notifications as read:', error);
    }
};

const logout = () => {
    router.post(route('logout'));
};

const getNotificationIcon = (type) => {
    switch (type) {
        case 'donation':
            return 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        case 'application':
            return 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
        case 'contact':
            return 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z';
        default:
            return 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
    }
};

const getNotificationColor = (type) => {
    switch (type) {
        case 'donation':
            return 'text-green-600';
        case 'application':
            return 'text-blue-600';
        case 'contact':
            return 'text-purple-600';
        default:
            return 'text-gray-600';
    }
};

// Handle click outside to close dropdowns
const handleClickOutside = (event) => {
    // Check if click is outside notification dropdown
    if (showNotifications.value) {
        const notificationDropdown = event.target.closest('.notification-dropdown');
        const notificationButton = event.target.closest('.notification-button');
        if (!notificationDropdown && !notificationButton) {
            showNotifications.value = false;
        }
    }
    
    // Check if click is outside user menu dropdown
    if (showUserMenu.value) {
        const userDropdown = event.target.closest('.user-dropdown');
        const userButton = event.target.closest('.user-button');
        if (!userDropdown && !userButton) {
            showUserMenu.value = false;
        }
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    // Fetch notifications on component mount
    fetchNotifications();
    
    // Refresh notifications every 60 seconds
    const notificationInterval = setInterval(fetchNotifications, 60000);
    
    // Store interval ID for cleanup
    window.notificationInterval = notificationInterval;
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    // Clear notification refresh interval
    if (window.notificationInterval) {
        clearInterval(window.notificationInterval);
    }
});
</script>

<template>
    <div class="sticky top-0 z-10 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
        <!-- Mobile menu button -->
        <button 
            type="button" 
            class="-m-2.5 p-2.5 text-gray-700 lg:hidden"
            @click="$emit('toggle-sidebar')"
        >
            <span class="sr-only">Open sidebar</span>
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <!-- Separator -->
        <div class="h-6 w-px bg-gray-200 lg:hidden" aria-hidden="true" />

        <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
            <!-- Search -->
            <form class="relative flex flex-1" action="#" method="GET">
                <label for="search-field" class="sr-only">Search</label>
                <svg 
                    class="pointer-events-none absolute inset-y-0 left-0 h-full w-5 text-gray-400 ml-3" 
                    viewBox="0 0 20 20" 
                    fill="currentColor"
                >
                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                </svg>
                <input 
                    id="search-field" 
                    class="block h-full w-full border-0 py-0 pl-10 pr-0 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm bg-transparent" 
                    placeholder="Search donations, users, projects..." 
                    type="search" 
                    name="search"
                >
            </form>
            
            <div class="flex items-center gap-x-4 lg:gap-x-6">
                <!-- Quick Stats -->
                <div class="hidden sm:flex items-center gap-x-6 text-sm">
                    <div class="flex items-center gap-x-2">
                        <div class="h-2 w-2 bg-green-400 rounded-full"></div>
                        <span class="text-gray-600">System Online</span>
                    </div>
                    <div class="text-gray-600">
                        Last backup: <span class="font-medium">2 hours ago</span>
                    </div>
                </div>

                <!-- Notifications button -->
                <div class="relative">
                    <button 
                        type="button" 
                        class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500 relative notification-button"
                        @click="showNotifications = !showNotifications"
                    >
                        <span class="sr-only">View notifications</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        
                        <!-- Notification badge -->
                        <span 
                            v-if="unreadCount > 0"
                            class="absolute -top-1 -right-1 h-5 w-5 rounded-full bg-red-500 flex items-center justify-center text-xs font-medium text-white"
                        >
                            {{ unreadCount > 9 ? '9+' : unreadCount }}
                        </span>
                    </button>

                    <!-- Notifications dropdown -->
                    <div 
                        v-if="showNotifications"
                        class="absolute right-0 z-20 mt-2 w-80 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none notification-dropdown"
                    >
                        <div class="px-4 py-2 border-b border-gray-100">
                            <h3 class="text-sm font-medium text-gray-900">Notifications</h3>
                        </div>
                        
                        <div v-if="isLoadingNotifications" class="px-4 py-8 text-center">
                            <div class="inline-flex items-center">
                                <svg class="animate-spin h-5 w-5 text-gray-400 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-gray-500">Loading notifications...</span>
                            </div>
                        </div>
                        
                        <div v-else-if="notifications.length === 0" class="px-4 py-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No notifications</p>
                        </div>
                        
                        <div v-else class="max-h-96 overflow-y-auto">
                            <div 
                                v-for="notification in notifications" 
                                :key="notification.id"
                                class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-l-4"
                                :class="notification.unread ? 'border-l-sky-500 bg-sky-50' : 'border-l-transparent'"
                                @click="markAsRead(notification)"
                            >
                                <div class="flex items-start gap-x-3">
                                    <div 
                                        class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center"
                                        :class="notification.unread ? 'bg-sky-100' : 'bg-gray-100'"
                                    >
                                        <svg 
                                            class="h-4 w-4" 
                                            :class="getNotificationColor(notification.type)"
                                            fill="none" 
                                            viewBox="0 0 24 24" 
                                            stroke-width="1.5" 
                                            stroke="currentColor"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" :d="getNotificationIcon(notification.type)" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
                                        <p class="text-sm text-gray-600 truncate">{{ notification.message }}</p>
                                        <p class="text-xs text-gray-500 mt-1">{{ notification.time }}</p>
                                    </div>
                                    <div v-if="notification.unread" class="flex-shrink-0">
                                        <div class="h-2 w-2 bg-sky-500 rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="px-4 py-2 border-t border-gray-100 flex items-center justify-between">
                            <Link href="/admin/notifications" class="text-sm text-sky-600 hover:text-sky-700">
                                View all notifications
                            </Link>
                            <button 
                                v-if="unreadCount > 0"
                                @click="markAllAsRead"
                                class="text-sm text-gray-600 hover:text-gray-700"
                            >
                                Mark all as read
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Separator -->
                <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200" />

                <!-- Profile dropdown -->
                <div class="relative">
                    <button 
                        type="button" 
                        class="-m-1.5 flex items-center p-1.5 hover:bg-gray-50 rounded-lg transition-colors user-button"
                        @click="showUserMenu = !showUserMenu"
                    >
                        <span class="sr-only">Open user menu</span>
                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center">
                            <span class="text-sm font-medium text-white">
                                {{ user?.name?.charAt(0).toUpperCase() }}
                            </span>
                        </div>
                        <span class="hidden lg:flex lg:items-center ml-3">
                            <span class="text-sm font-semibold leading-6 text-gray-900">{{ user?.name }}</span>
                            <svg class="ml-2 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    </button>

                    <!-- User menu dropdown -->
                    <div 
                        v-if="showUserMenu"
                        class="absolute right-0 z-20 mt-2 w-56 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none user-dropdown"
                    >
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900">{{ user?.name }}</p>
                            <p class="text-sm text-gray-600">{{ user?.email }}</p>
                            <p class="text-xs text-sky-600 font-medium mt-1">{{ user?.county }} CPC</p>
                        </div>
                        
                        <Link 
                            href="/admin/profile" 
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50"
                            @click="showUserMenu = false"
                        >
                            <svg class="mr-3 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            Your Profile
                        </Link>
                        
                        <div class="border-t border-gray-100 my-1"></div>
                        
                        <button 
                            @click="logout"
                            class="flex w-full items-center px-4 py-2 text-sm text-red-700 hover:bg-red-50"
                        >
                            <svg class="mr-3 h-4 w-4 text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                            </svg>
                            Sign out
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>