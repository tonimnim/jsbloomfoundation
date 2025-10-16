<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    messages: Object,
    stats: Object,
    filters: Object
});

// Selected messages for bulk actions
const selectedMessages = ref([]);
const selectAll = ref(false);

// Search and filter form
const searchForm = useForm({
    search: props.filters.search || '',
    status: props.filters.status || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || ''
});

// Debounced search
let searchTimeout;
const search = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.contacts.index'), searchForm.data(), {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
};

// Watch for changes
watch(() => searchForm.search, search);
watch(() => [searchForm.status, searchForm.date_from, searchForm.date_to], () => {
    router.get(route('admin.contacts.index'), searchForm.data(), {
        preserveState: true,
        preserveScroll: true,
    });
});

// Toggle select all
const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedMessages.value = props.messages.data.map(msg => msg.id);
    } else {
        selectedMessages.value = [];
    }
};

// Check if message is selected
const isSelected = (id) => selectedMessages.value.includes(id);

// Toggle message selection
const toggleSelection = (id) => {
    const index = selectedMessages.value.indexOf(id);
    if (index > -1) {
        selectedMessages.value.splice(index, 1);
    } else {
        selectedMessages.value.push(id);
    }
};

// Bulk actions
const bulkMarkRead = () => {
    if (selectedMessages.value.length === 0) return;
    
    router.post(route('admin.contacts.bulk-mark-read'), {
        ids: selectedMessages.value
    }, {
        onSuccess: () => {
            selectedMessages.value = [];
            selectAll.value = false;
        }
    });
};

const bulkDelete = () => {
    if (selectedMessages.value.length === 0) return;
    
    if (confirm(`Are you sure you want to delete ${selectedMessages.value.length} messages?`)) {
        router.post(route('admin.contacts.bulk-delete'), {
            ids: selectedMessages.value
        }, {
            onSuccess: () => {
                selectedMessages.value = [];
                selectAll.value = false;
            }
        });
    }
};

// Toggle read status
const toggleRead = (message) => {
    router.put(route('admin.contacts.toggle-read', message.id));
};

// Delete message
const deleteMessage = (message) => {
    if (confirm('Are you sure you want to delete this message?')) {
        router.delete(route('admin.contacts.destroy', message.id));
    }
};

// Format date
const formatDate = (date) => {
    if (!date) return '';
    const d = new Date(date);
    const now = new Date();
    const diffInHours = (now - d) / (1000 * 60 * 60);
    
    if (diffInHours < 24) {
        return d.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });
    } else if (diffInHours < 168) { // 7 days
        return d.toLocaleDateString('en-US', { 
            weekday: 'short',
            month: 'short',
            day: 'numeric'
        });
    } else {
        return d.toLocaleDateString('en-US', { 
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
};

// Truncate text
const truncate = (text, length = 100) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};
</script>

<template>
    <AdminLayout>
        <Head title="Contact Messages - Admin" />
        
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Contact Messages</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage messages from the contact form</p>
                </div>
            </div>
        </template>
        
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-gray-500 text-sm">Total Messages</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.total_messages }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-gray-500 text-sm">Unread</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.unread_messages }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-gray-500 text-sm">Today</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.today_messages }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-gray-500 text-sm">This Week</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.this_week }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters and Search -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input
                        id="search"
                        v-model="searchForm.search"
                        type="text"
                        placeholder="Search by name, email, subject, or message..."
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
                        <option value="">All Messages</option>
                        <option value="unread">Unread Only</option>
                        <option value="read">Read Only</option>
                    </select>
                </div>
                
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                    <input
                        id="date_from"
                        v-model="searchForm.date_from"
                        type="date"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
                
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                    <input
                        id="date_to"
                        v-model="searchForm.date_to"
                        type="date"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
            </div>
            
            <!-- Bulk Actions -->
            <div v-if="selectedMessages.length > 0" class="mt-4 pt-4 border-t flex items-center space-x-4">
                <span class="text-sm text-gray-600">
                    {{ selectedMessages.length }} selected
                </span>
                <button
                    @click="bulkMarkRead"
                    class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded hover:bg-gray-200"
                >
                    Mark as Read
                </button>
                <button
                    @click="bulkDelete"
                    class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200"
                >
                    Delete Selected
                </button>
            </div>
        </div>
        
        <!-- Messages List -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div v-if="messages.data.length > 0">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input
                                    v-model="selectAll"
                                    @change="toggleSelectAll"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Sender
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Subject
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Message
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr 
                            v-for="message in messages.data" 
                            :key="message.id"
                            :class="{ 'bg-blue-50': !message.is_read }"
                            class="hover:bg-gray-50 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <input
                                    :checked="isSelected(message.id)"
                                    @change="toggleSelection(message.id)"
                                    type="checkbox"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium" :class="message.is_read ? 'text-gray-900' : 'text-gray-900 font-semibold'">
                                        {{ message.first_name }} {{ message.last_name }}
                                    </div>
                                    <div class="text-sm text-gray-500">{{ message.email }}</div>
                                    <div v-if="message.phone" class="text-sm text-gray-500">{{ message.phone }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm" :class="message.is_read ? 'text-gray-900' : 'text-gray-900 font-semibold'">
                                    {{ message.subject }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-600 max-w-xs">
                                    {{ truncate(message.message, 150) }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(message.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <Link
                                        :href="route('admin.contacts.show', message.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="View Message"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </Link>
                                    <button
                                        @click="toggleRead(message)"
                                        :class="message.is_read ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'"
                                        :title="message.is_read ? 'Mark as Unread' : 'Mark as Read'"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path v-if="!message.is_read" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 19v-8.93a2 2 0 01.89-1.664l7-4.666a2 2 0 012.22 0l7 4.666A2 2 0 0121 10.07V19M3 19a2 2 0 002 2h14a2 2 0 002-2M3 19l6.75-4.5M21 19l-6.75-4.5M3 10l6.75 4.5M21 10l-6.75 4.5m0 0l-1.14.76a2 2 0 01-2.22 0l-1.14-.76" />
                                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteMessage(message)"
                                        class="text-red-600 hover:text-red-900"
                                        title="Delete Message"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-else class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No messages found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
            </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="messages.links.length > 3" class="mt-6">
            <nav class="flex items-center justify-between">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ messages.from }}</span>
                            to
                            <span class="font-medium">{{ messages.to }}</span>
                            of
                            <span class="font-medium">{{ messages.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <Link
                                v-for="link in messages.links"
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