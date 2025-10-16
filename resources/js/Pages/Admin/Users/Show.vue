<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    user: Object
});

// Toggle user status
const toggleStatus = () => {
    router.put(route('admin.users.toggle-status', props.user.id));
};

// Delete user
const deleteUser = () => {
    if (confirm(`Are you sure you want to delete ${props.user.name}? This action cannot be undone.`)) {
        router.delete(route('admin.users.destroy', props.user.id));
    }
};

// Verify email
const verifyEmail = () => {
    if (confirm(`Manually verify email for ${props.user.name}?`)) {
        router.put(route('admin.users.verify-email', props.user.id));
    }
};

// Format date
const formatDate = (date) => {
    if (!date) return 'Never';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Get role badge color
const getRoleBadge = (role) => {
    return role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <AdminLayout>
        <Head :title="`${user.name} - User Details`" />
        
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">User Details</h1>
                    <p class="mt-1 text-sm text-gray-600">View and manage user information</p>
                </div>
                <Link
                    :href="route('admin.users.index')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    Back to Users
                </Link>
            </div>
        </template>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <img 
                                v-if="user.profile_picture"
                                :src="user.profile_picture" 
                                :alt="user.name"
                                class="h-20 w-20 rounded-full object-cover"
                            >
                            <div v-else class="h-20 w-20 rounded-full bg-gray-300 flex items-center justify-center">
                                <span class="text-2xl text-gray-600 font-medium">{{ user.name.charAt(0).toUpperCase() }}</span>
                            </div>
                        </div>
                        
                        <div class="flex-1 space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Full Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ user.name }}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Email Address</label>
                                    <div class="mt-1 flex items-center">
                                        <p class="text-sm text-gray-900">{{ user.email }}</p>
                                        <span v-if="user.email_verified_at" class="ml-2 text-green-600" title="Verified">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                        <span v-else class="ml-2 text-yellow-600" title="Unverified">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        </span>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-500">Role</label>
                                    <span :class="getRoleBadge(user.role)" class="mt-1 inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                        {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contact & Location -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Contact & Location</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Phone Number</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.phone || 'Not provided' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">County</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.county || 'Not specified' }}</p>
                        </div>
                        
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-500">Organization</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.organization || 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Account Information -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Account Information</h3>
                    
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Account Status</label>
                                <span :class="user.is_active ? 'text-green-600' : 'text-red-600'" class="mt-1 text-sm font-medium">
                                    {{ user.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Email Verification</label>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ user.email_verified_at ? formatDate(user.email_verified_at) : 'Not verified' }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Account Created</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDate(user.created_at) }}</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDate(user.updated_at) }}</p>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Last Login</label>
                            <p class="mt-1 text-sm text-gray-900">{{ formatDate(user.last_login_at) }}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar Actions -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                    
                    <div class="space-y-3">
                        <Link
                            :href="route('admin.users.edit', user.id)"
                            class="block w-full px-4 py-2 bg-blue-600 text-white text-center rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Edit User
                        </Link>
                        
                        <button
                            @click="toggleStatus"
                            :class="[
                                'block w-full px-4 py-2 text-center rounded-lg transition-colors',
                                user.is_active
                                    ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                                    : 'bg-green-100 text-green-700 hover:bg-green-200'
                            ]"
                        >
                            {{ user.is_active ? 'Deactivate User' : 'Activate User' }}
                        </button>
                        
                        <button
                            v-if="!user.email_verified_at"
                            @click="verifyEmail"
                            class="block w-full px-4 py-2 bg-green-100 text-green-700 text-center rounded-lg hover:bg-green-200 transition-colors"
                        >
                            Verify Email
                        </button>
                        
                        <button
                            @click="deleteUser"
                            class="block w-full px-4 py-2 bg-red-100 text-red-700 text-center rounded-lg hover:bg-red-200 transition-colors"
                        >
                            Delete User
                        </button>
                    </div>
                </div>
                
                <!-- Status Summary -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Status Summary</h4>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Account</span>
                            <span :class="user.is_active ? 'text-green-600' : 'text-red-600'" class="font-medium">
                                {{ user.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Email</span>
                            <span :class="user.email_verified_at ? 'text-green-600' : 'text-yellow-600'" class="font-medium">
                                {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500">Role</span>
                            <span class="font-medium text-gray-900">
                                {{ user.role.charAt(0).toUpperCase() + user.role.slice(1) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>