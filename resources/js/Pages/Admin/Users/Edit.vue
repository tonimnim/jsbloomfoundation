<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    user: Object,
    counties: Array,
    roles: Array
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    county: props.user.county || '',
    phone: props.user.phone || '',
    organization: props.user.organization || '',
    is_active: props.user.is_active,
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id));
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Edit ${user.name} - Admin`" />
        
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit User</h1>
                    <p class="mt-1 text-sm text-gray-600">Update user information and settings</p>
                </div>
                <Link
                    :href="route('admin.users.index')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    Back to Users
                </Link>
            </div>
        </template>
        
        <form @submit.prevent="submit">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        
                        <div class="space-y-4">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': form.errors.name }"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': form.errors.email }"
                                />
                                <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                            </div>
                            
                            <!-- Role -->
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                                    Role <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': form.errors.role }"
                                >
                                    <option v-for="role in roles" :key="role" :value="role">
                                        {{ role.charAt(0).toUpperCase() + role.slice(1) }}
                                    </option>
                                </select>
                                <p v-if="form.errors.role" class="mt-1 text-sm text-red-600">{{ form.errors.role }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Contact Information</h3>
                        
                        <div class="space-y-4">
                            <!-- Phone -->
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': form.errors.phone }"
                                    placeholder="+254 XXX XXX XXX"
                                />
                                <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                            </div>
                            
                            <!-- County -->
                            <div>
                                <label for="county" class="block text-sm font-medium text-gray-700 mb-2">
                                    County
                                </label>
                                <select
                                    id="county"
                                    v-model="form.county"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': form.errors.county }"
                                >
                                    <option value="">Select County</option>
                                    <option v-for="county in counties" :key="county" :value="county">
                                        {{ county }}
                                    </option>
                                </select>
                                <p v-if="form.errors.county" class="mt-1 text-sm text-red-600">{{ form.errors.county }}</p>
                            </div>
                            
                            <!-- Organization -->
                            <div>
                                <label for="organization" class="block text-sm font-medium text-gray-700 mb-2">
                                    Organization
                                </label>
                                <input
                                    id="organization"
                                    v-model="form.organization"
                                    type="text"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': form.errors.organization }"
                                    placeholder="Company or organization name"
                                />
                                <p v-if="form.errors.organization" class="mt-1 text-sm text-red-600">{{ form.errors.organization }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Account Settings -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Account Settings</h3>
                        
                        <div class="space-y-4">
                            <!-- Active Status -->
                            <div>
                                <label class="flex items-center">
                                    <input
                                        v-model="form.is_active"
                                        type="checkbox"
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                    />
                                    <span class="ml-2 text-sm text-gray-700">Account is active</span>
                                </label>
                                <p class="mt-1 text-xs text-gray-500">
                                    Inactive accounts cannot log in to the system.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Updating...</span>
                            <span v-else>Update User</span>
                        </button>
                        
                        <Link
                            :href="route('admin.users.show', user.id)"
                            class="block text-center mt-3 text-sm text-gray-600 hover:text-gray-900"
                        >
                            Cancel
                        </Link>
                    </div>
                    
                    <!-- User Info -->
                    <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                        <p><strong>User ID:</strong> {{ user.id }}</p>
                        <p><strong>Created:</strong> {{ new Date(user.created_at).toLocaleDateString() }}</p>
                        <p><strong>Last Updated:</strong> {{ new Date(user.updated_at).toLocaleDateString() }}</p>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>