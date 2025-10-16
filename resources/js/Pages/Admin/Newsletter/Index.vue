<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    subscribers: Object,
    stats: Object,
    filters: Object
});

// Search form
const searchForm = useForm({
    search: props.filters.search || '',
    status: props.filters.status || ''
});

// Debounced search
let searchTimeout;
const search = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.newsletter.index'), searchForm.data(), {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
};

// Watch for changes
watch(() => searchForm.search, search);
watch(() => searchForm.status, () => {
    router.get(route('admin.newsletter.index'), searchForm.data(), {
        preserveState: true,
        preserveScroll: true,
    });
});

// Export subscribers
const exportSubscribers = () => {
    window.location.href = route('admin.newsletter.export');
};

// Delete subscriber
const deleteSubscriber = (id) => {
    if (confirm('Are you sure you want to delete this subscriber?')) {
        router.delete(route('admin.newsletter.destroy', id));
    }
};

// Toggle status
const toggleStatus = (id) => {
    router.put(route('admin.newsletter.toggle-status', id));
};

// Format date
const formatDate = (date) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Newsletter Subscribers - Admin" />
        
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Newsletter Subscribers</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage your newsletter subscribers</p>
                </div>
                <button
                    @click="exportSubscribers"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                >
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export CSV
                </button>
            </div>
        </template>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-gray-500 text-sm">Total Subscribers</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.total_subscribers }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-gray-500 text-sm">Active</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.active_subscribers }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-red-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-gray-500 text-sm">Unsubscribed</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.unsubscribed }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-gray-500 text-sm">This Month</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.this_month }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input
                        id="search"
                        v-model="searchForm.search"
                        type="text"
                        placeholder="Search by email or name..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select
                        id="status"
                        v-model="searchForm.status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">All Subscribers</option>
                        <option value="active">Active Only</option>
                        <option value="inactive">Unsubscribed Only</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Subscribers Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div v-if="subscribers.data.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Subscriber
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Subscribed
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Unsubscribed
                            </th>
                            <th class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="subscriber in subscribers.data" :key="subscriber.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ subscriber.email }}</div>
                                    <div v-if="subscriber.name" class="text-sm text-gray-500">{{ subscriber.name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button
                                    @click="toggleStatus(subscriber.id)"
                                    :class="subscriber.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    class="px-2 py-1 text-xs font-semibold rounded-full cursor-pointer hover:opacity-80"
                                >
                                    {{ subscriber.is_active ? 'Active' : 'Unsubscribed' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(subscriber.subscribed_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(subscriber.unsubscribed_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button
                                    @click="deleteSubscriber(subscriber.id)"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No subscribers found</h3>
                <p class="mt-1 text-sm text-gray-500">Subscribers will appear here when people sign up for your newsletter.</p>
            </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="subscribers.links.length > 3" class="mt-6">
            <nav class="flex items-center justify-between">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ subscribers.from }}</span>
                            to
                            <span class="font-medium">{{ subscribers.to }}</span>
                            of
                            <span class="font-medium">{{ subscribers.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <Link
                                v-for="link in subscribers.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    link.active
                                        ? 'z-10 bg-blue-50 border-blue-500 text-blue-600'
                                        : link.url
                                            ? 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                                            : 'bg-gray-100 border-gray-300 text-gray-400 cursor-not-allowed',
                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                                ]"
                                :disabled="!link.url"
                                v-html="link.label"
                            />
                        </nav>
                    </div>
                </div>
            </nav>
        </div>
    </AdminLayout>
</template>