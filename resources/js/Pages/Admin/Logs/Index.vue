<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    accessLogs: Array,
    applicationLogs: Array,
    stats: Object,
});

const activeTab = ref('access');
const searchQuery = ref('');
const selectedLevel = ref('all');

const tabs = [
    { id: 'access', name: 'Access Logs', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
    { id: 'application', name: 'Application Logs', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
];

const logLevels = [
    { value: 'all', label: 'All Levels' },
    { value: 'emergency', label: 'Emergency' },
    { value: 'alert', label: 'Alert' },
    { value: 'critical', label: 'Critical' },
    { value: 'error', label: 'Error' },
    { value: 'warning', label: 'Warning' },
    { value: 'notice', label: 'Notice' },
    { value: 'info', label: 'Info' },
    { value: 'debug', label: 'Debug' },
];

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
};

const getLevelColor = (level) => {
    const colors = {
        emergency: 'bg-red-600 text-white',
        alert: 'bg-red-500 text-white',
        critical: 'bg-red-400 text-white',
        error: 'bg-red-300 text-red-900',
        warning: 'bg-yellow-300 text-yellow-900',
        notice: 'bg-blue-300 text-blue-900',
        info: 'bg-green-300 text-green-900',
        debug: 'bg-gray-300 text-gray-900',
    };
    return colors[level?.toLowerCase()] || 'bg-gray-200 text-gray-900';
};

const getStatusColor = (status) => {
    if (status >= 200 && status < 300) return 'text-green-600';
    if (status >= 300 && status < 400) return 'text-blue-600';
    if (status >= 400 && status < 500) return 'text-yellow-600';
    if (status >= 500) return 'text-red-600';
    return 'text-gray-600';
};

const filteredAccessLogs = computed(() => {
    if (activeTab.value !== 'access') return [];
    
    let logs = props.accessLogs || [];
    
    if (searchQuery.value) {
        logs = logs.filter(log => 
            log.ip?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.user_agent?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.method?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.url?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.user?.name?.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }
    
    return logs;
});

const filteredApplicationLogs = computed(() => {
    if (activeTab.value !== 'application') return [];
    
    let logs = props.applicationLogs || [];
    
    if (selectedLevel.value !== 'all') {
        logs = logs.filter(log => log.level?.toLowerCase() === selectedLevel.value);
    }
    
    if (searchQuery.value) {
        logs = logs.filter(log => 
            log.message?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.context?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            log.channel?.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }
    
    return logs;
});

const truncateText = (text, length = 100) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
};
</script>

<template>
    <Head title="System Logs" />

    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">System Logs</h1>
                <p class="text-gray-600">Monitor access logs and application activity</p>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-blue-100 rounded-lg">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Requests</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats?.total_requests || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-green-100 rounded-lg">
                                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Successful (2xx)</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats?.successful_requests || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-red-100 rounded-lg">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Errors (4xx/5xx)</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats?.error_requests || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-purple-100 rounded-lg">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Unique Visitors</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats?.unique_visitors || 0 }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs and Content -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6">
                        <button
                            v-for="tab in tabs"
                            :key="tab.id"
                            @click="activeTab = tab.id"
                            :class="[
                                activeTab === tab.id
                                    ? 'border-sky-500 text-sky-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center'
                            ]"
                        >
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon" />
                            </svg>
                            {{ tab.name }}
                        </button>
                    </nav>
                </div>

                <!-- Filters -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                        <div class="flex items-center space-x-4">
                            <!-- Log Level Filter (for Application Logs) -->
                            <div v-if="activeTab === 'application'" class="min-w-0 flex-1">
                                <select 
                                    v-model="selectedLevel"
                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                >
                                    <option v-for="level in logLevels" :key="level.value" :value="level.value">
                                        {{ level.label }}
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Search -->
                        <div class="min-w-0 flex-1 max-w-sm">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <input 
                                    v-model="searchQuery"
                                    type="text"
                                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                    :placeholder="activeTab === 'access' ? 'Search IP, URL, user...' : 'Search message, context...'"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Access Logs Table -->
                <div v-if="activeTab === 'access'" class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Timestamp
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    User
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Request
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    IP Address
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="log in filteredAccessLogs" :key="log.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(log.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div v-if="log.user" class="text-sm">
                                        <div class="font-medium text-gray-900">{{ log.user.name }}</div>
                                        <div class="text-gray-500">{{ log.user.email }}</div>
                                    </div>
                                    <span v-else class="text-sm text-gray-500">Guest</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm">
                                        <span class="font-medium text-gray-900">{{ log.method }}</span>
                                        <span class="text-gray-500">{{ truncateText(log.url, 50) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getStatusColor(log.status_code)" class="text-sm font-medium">
                                        {{ log.status_code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ log.ip_address }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="filteredAccessLogs.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No access logs found</h3>
                        <p class="mt-1 text-sm text-gray-500">Access logs will appear here as users interact with the system.</p>
                    </div>
                </div>

                <!-- Application Logs Table -->
                <div v-if="activeTab === 'application'" class="overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Timestamp
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Level
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Channel
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Message
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="log in filteredApplicationLogs" :key="log.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ formatDate(log.created_at) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="getLevelColor(log.level)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full uppercase">
                                        {{ log.level }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ log.channel || 'app' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        {{ truncateText(log.message, 100) }}
                                    </div>
                                    <div v-if="log.context" class="text-xs text-gray-500 mt-1">
                                        {{ truncateText(log.context, 80) }}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-if="filteredApplicationLogs.length === 0" class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No application logs found</h3>
                        <p class="mt-1 text-sm text-gray-500">Application logs will appear here as the system runs.</p>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>