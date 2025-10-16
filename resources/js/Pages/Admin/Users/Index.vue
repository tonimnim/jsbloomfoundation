<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    users: Object,
    stats: Object,
    filters: Object
});

// Search and filter form
const searchForm = useForm({
    search: props.filters.search || '',
    role: props.filters.role || '',
    status: props.filters.status || '',
    verified: props.filters.verified || ''
});

// Debounced search
let searchTimeout;
const search = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.users.index'), searchForm.data(), {
            preserveState: true,
            preserveScroll: true,
        });
    }, 300);
};

// Watch for changes
watch(() => searchForm.search, search);
watch(() => [searchForm.role, searchForm.status, searchForm.verified], () => {
    router.get(route('admin.users.index'), searchForm.data(), {
        preserveState: true,
        preserveScroll: true,
    });
});

// Toggle user status
const toggleStatus = (user) => {
    router.put(route('admin.users.toggle-status', user.id));
};

// Delete user
const deleteUser = (user) => {
    if (confirm(`Are you sure you want to delete ${user.name}? This action cannot be undone.`)) {
        router.delete(route('admin.users.destroy', user.id));
    }
};

// Verify email
const verifyEmail = (user) => {
    if (confirm(`Manually verify email for ${user.name}?`)) {
        router.put(route('admin.users.verify-email', user.id));
    }
};

// Get role badge color
const getRoleBadge = (role) => {
    return role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800';
};

// Format date
const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="All Users - Admin" />
        
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">All Users</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage all registered users</p>
                </div>
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
                        <p class="text-gray-500 text-sm">Total Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.total_users }}</p>
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
                        <p class="text-gray-500 text-sm">Active Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.active_users }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-gray-500 text-sm">Admin Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.admin_users }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-yellow-100 rounded-lg p-3">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-gray-500 text-sm">Verified Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ stats.verified_users }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <input
                        id="search"
                        v-model="searchForm.search"
                        type="text"
                        placeholder="Search by name, email, county..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                </div>
                
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select
                        id="role"
                        v-model="searchForm.role"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">All Roles</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select
                        id="status"
                        v-model="searchForm.status"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                
                <div>
                    <label for="verified" class="block text-sm font-medium text-gray-700 mb-2">Email Verified</label>
                    <select
                        id="verified"
                        v-model="searchForm.verified"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">All</option>
                        <option value="yes">Verified</option>
                        <option value="no">Not Verified</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div v-if="users.data.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                User
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Role
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Location
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Joined
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Last Login
                            </th>
                            <th class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="user in users.data" :key="user.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img 
                                            v-if="user.profile_picture"
                                            :src="user.profile_picture" 
                                            :alt="user.name"
                                            class="h-10 w-10 rounded-full object-cover"
                                        >
                                        <div v-else class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-gray-600 font-medium">{{ user.name.charAt(0).toUpperCase() }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                        <div class="text-sm text-gray-500">{{ user.email }}</div>
                                        <div v-if="!user.email_verified_at" class="flex items-center mt-1">
                                            <svg class="w-4 h-4 text-yellow-500 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                            <span class="text-xs text-yellow-600">Unverified</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getRoleBadge(user.role)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ user.county || 'Not specified' }}</div>
                                <div v-if="user.organization" class="text-sm text-gray-500">{{ user.organization }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button
                                    @click="toggleStatus(user)"
                                    :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    class="px-2 py-1 text-xs font-semibold rounded-full cursor-pointer hover:opacity-80"
                                >
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(user.created_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ formatDate(user.last_login_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end space-x-2">
                                    <Link
                                        :href="route('admin.users.show', user.id)"
                                        class="text-gray-600 hover:text-gray-900"
                                        title="View Details"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('admin.users.edit', user.id)"
                                        class="text-blue-600 hover:text-blue-900"
                                        title="Edit User"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Link>
                                    <button
                                        v-if="!user.email_verified_at"
                                        @click="verifyEmail(user)"
                                        class="text-green-600 hover:text-green-900"
                                        title="Verify Email"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteUser(user)"
                                        class="text-red-600 hover:text-red-900"
                                        title="Delete User"
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
            </div>
        </div>
        
        <!-- Pagination -->
        <div v-if="users.links.length > 3" class="mt-6">
            <nav class="flex items-center justify-between">
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium">{{ users.from }}</span>
                            to
                            <span class="font-medium">{{ users.to }}</span>
                            of
                            <span class="font-medium">{{ users.total }}</span>
                            results
                        </p>
                    </div>
                    <div>
                        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                            <Link
                                v-for="link in users.links"
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