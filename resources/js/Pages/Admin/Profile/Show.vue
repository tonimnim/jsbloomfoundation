<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    counties: Array,
});

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const formatCauses = (causes) => {
    if (!causes || causes.length === 0) return 'None selected';
    return causes.join(', ');
};
</script>

<template>
    <Head title="Admin Profile" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Profile</h1>
                    <p class="text-gray-600">View and manage your profile information</p>
                </div>
                <Link 
                    :href="route('admin.profile.edit')"
                    class="inline-flex items-center px-4 py-2 bg-sky-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 focus:bg-sky-700 active:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                    </svg>
                    Edit Profile
                </Link>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-8">
            <!-- Profile Header -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-8">
                    <div class="flex items-center space-x-6">
                        <div class="flex-shrink-0">
                            <div v-if="user.profile_picture" class="h-24 w-24 rounded-full overflow-hidden">
                                <img 
                                    :src="`/storage/${user.profile_picture}`" 
                                    :alt="user.name"
                                    class="h-full w-full object-cover"
                                >
                            </div>
                            <div v-else class="h-24 w-24 rounded-full bg-gradient-to-br from-sky-400 to-blue-600 flex items-center justify-center">
                                <span class="text-2xl font-bold text-white">{{ getInitials(user.name) }}</span>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-2xl font-bold text-gray-900">{{ user.name }}</h2>
                            <p class="text-gray-600">{{ user.email }}</p>
                            <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-800">
                                    {{ user.role === 'admin' ? 'Administrator' : 'User' }}
                                </span>
                                <span v-if="user.county">{{ user.county }} County</span>
                                <span>Member since {{ user.created_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Personal Information -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Full Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email Address</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Phone Number</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.phone || 'Not provided' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">County</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.county || 'Not specified' }}</p>
                        </div>
                        <div v-if="user.organization">
                            <label class="block text-sm font-medium text-gray-500">Organization</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.organization }}</p>
                        </div>
                        <div v-if="user.address">
                            <label class="block text-sm font-medium text-gray-500">Address</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.address }}</p>
                        </div>
                    </div>
                </div>

                <!-- Account Settings -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Account Settings</h3>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Account Role</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.role === 'admin' ? 'Administrator' : 'User' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Last Login</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.last_login_at || 'Never' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Account Created</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.created_at }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email Notifications</label>
                            <div class="mt-1 flex items-center">
                                <svg v-if="user.email_notifications" class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <svg v-else class="h-4 w-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-900">{{ user.email_notifications ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">SMS Notifications</label>
                            <div class="mt-1 flex items-center">
                                <svg v-if="user.sms_notifications" class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <svg v-else class="h-4 w-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-900">{{ user.sms_notifications ? 'Enabled' : 'Disabled' }}</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Newsletter Subscription</label>
                            <div class="mt-1 flex items-center">
                                <svg v-if="user.newsletter_subscribed" class="h-4 w-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <svg v-else class="h-4 w-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm text-gray-900">{{ user.newsletter_subscribed ? 'Subscribed' : 'Not subscribed' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preferred Causes -->
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 lg:col-span-2">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Preferred Causes</h3>
                    </div>
                    <div class="px-6 py-4">
                        <p class="text-sm text-gray-900">{{ formatCauses(user.preferred_causes) }}</p>
                    </div>
                </div>

                <!-- Donation Summary (for non-admin users) -->
                <div v-if="user.role !== 'admin' && (user.total_donations || user.last_donation)" class="bg-white shadow-sm rounded-lg border border-gray-200 lg:col-span-2">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Donation Summary</h3>
                    </div>
                    <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-if="user.total_donations">
                            <label class="block text-sm font-medium text-gray-500">Total Donated</label>
                            <p class="mt-1 text-lg font-semibold text-green-600">KES {{ user.total_donations?.toLocaleString() }}</p>
                        </div>
                        <div v-if="user.last_donation">
                            <label class="block text-sm font-medium text-gray-500">Last Donation</label>
                            <p class="mt-1 text-sm text-gray-900">{{ user.last_donation }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>