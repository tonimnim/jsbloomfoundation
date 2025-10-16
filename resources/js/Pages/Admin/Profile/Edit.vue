<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    user: Object,
    counties: Array,
    causes: Array,
});

const profileForm = useForm({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone,
    county: props.user.county,
    organization: props.user.organization,
    address: props.user.address,
    profile_picture: null,
    newsletter_subscribed: props.user.newsletter_subscribed,
    email_notifications: props.user.email_notifications,
    sms_notifications: props.user.sms_notifications,
    preferred_causes: props.user.preferred_causes || [],
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const profilePictureInput = ref(null);
const profilePicturePreview = ref(null);

const updateProfile = () => {
    profileForm.transform((data) => ({
        ...data,
        _method: 'PUT'
    })).post(route('admin.profile.update'), {
        onSuccess: () => {
            // Reset profile picture after successful upload
            profileForm.profile_picture = null;
            profilePicturePreview.value = null;
        }
    });
};

const updatePassword = () => {
    passwordForm.put(route('admin.profile.password.update'), {
        onSuccess: () => {
            passwordForm.reset();
        }
    });
};

const selectNewProfilePicture = () => {
    profilePictureInput.value.click();
};

const updateProfilePicturePreview = () => {
    const file = profilePictureInput.value.files[0];
    
    if (!file) return;
    
    profileForm.profile_picture = file;
    
    const reader = new FileReader();
    reader.onload = (e) => {
        profilePicturePreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
};

const deleteProfilePicture = () => {
    profileForm.delete(route('admin.profile.profile-picture.delete'), {
        preserveScroll: true,
    });
};

const getInitials = (name) => {
    return name.split(' ').map(n => n[0]).join('').toUpperCase();
};

const toggleCause = (cause) => {
    const index = profileForm.preferred_causes.indexOf(cause);
    if (index > -1) {
        profileForm.preferred_causes.splice(index, 1);
    } else {
        profileForm.preferred_causes.push(cause);
    }
};
</script>

<template>
    <Head title="Edit Profile" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Profile</h1>
                    <p class="text-gray-600">Update your profile information and preferences</p>
                </div>
                <Link 
                    :href="route('admin.profile.show')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3" />
                    </svg>
                    Back to Profile
                </Link>
            </div>
        </template>

        <div class="max-w-4xl mx-auto space-y-8">
            <!-- Profile Picture Section -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Profile Picture</h3>
                </div>
                <div class="px-6 py-6">
                    <div class="flex items-center space-x-6">
                        <div class="flex-shrink-0">
                            <div v-if="profilePicturePreview" class="h-24 w-24 rounded-full overflow-hidden">
                                <img 
                                    :src="profilePicturePreview" 
                                    :alt="user.name"
                                    class="h-full w-full object-cover"
                                >
                            </div>
                            <div v-else-if="user.profile_picture" class="h-24 w-24 rounded-full overflow-hidden">
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
                        <div class="flex-1">
                            <input 
                                ref="profilePictureInput" 
                                type="file" 
                                class="hidden" 
                                accept="image/*"
                                @change="updateProfilePicturePreview"
                            >
                            <button 
                                type="button"
                                @click="selectNewProfilePicture"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3"
                            >
                                Select A New Photo
                            </button>
                            <button 
                                v-if="user.profile_picture"
                                type="button"
                                @click="deleteProfilePicture"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 transition ease-in-out duration-150"
                            >
                                Remove Photo
                            </button>
                            <p class="mt-2 text-sm text-gray-500">JPG, PNG, GIF up to 2MB</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Information Form -->
            <form @submit.prevent="updateProfile" class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Profile Information</h3>
                </div>
                <div class="px-6 py-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                            <input 
                                id="name"
                                v-model="profileForm.name"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                required
                            >
                            <div v-if="profileForm.errors.name" class="mt-2 text-sm text-red-600">{{ profileForm.errors.name }}</div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input 
                                id="email"
                                v-model="profileForm.email"
                                type="email"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                required
                            >
                            <div v-if="profileForm.errors.email" class="mt-2 text-sm text-red-600">{{ profileForm.errors.email }}</div>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input 
                                id="phone"
                                v-model="profileForm.phone"
                                type="tel"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                placeholder="+254 700 000 000"
                            >
                            <div v-if="profileForm.errors.phone" class="mt-2 text-sm text-red-600">{{ profileForm.errors.phone }}</div>
                        </div>

                        <!-- County -->
                        <div>
                            <label for="county" class="block text-sm font-medium text-gray-700">County</label>
                            <select 
                                id="county"
                                v-model="profileForm.county"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                            >
                                <option value="">Select County</option>
                                <option v-for="county in counties" :key="county" :value="county">{{ county }}</option>
                            </select>
                            <div v-if="profileForm.errors.county" class="mt-2 text-sm text-red-600">{{ profileForm.errors.county }}</div>
                        </div>
                    </div>

                    <!-- Organization -->
                    <div>
                        <label for="organization" class="block text-sm font-medium text-gray-700">Organization (Optional)</label>
                        <input 
                            id="organization"
                            v-model="profileForm.organization"
                            type="text"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                        >
                        <div v-if="profileForm.errors.organization" class="mt-2 text-sm text-red-600">{{ profileForm.errors.organization }}</div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700">Address (Optional)</label>
                        <textarea 
                            id="address"
                            v-model="profileForm.address"
                            rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                        ></textarea>
                        <div v-if="profileForm.errors.address" class="mt-2 text-sm text-red-600">{{ profileForm.errors.address }}</div>
                    </div>

                    <!-- Preferred Causes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Preferred Causes</label>
                        <p class="text-sm text-gray-500 mb-3">Select the causes you're most interested in supporting</p>
                        <div class="space-y-2">
                            <label v-for="cause in causes" :key="cause" class="flex items-center">
                                <input 
                                    type="checkbox"
                                    :checked="profileForm.preferred_causes.includes(cause)"
                                    @change="toggleCause(cause)"
                                    class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-gray-300 rounded"
                                >
                                <span class="ml-2 text-sm text-gray-700">{{ cause }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Notification Preferences -->
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Notification Preferences</h4>
                        <div class="space-y-4">
                            <label class="flex items-center">
                                <input 
                                    v-model="profileForm.newsletter_subscribed"
                                    type="checkbox"
                                    class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-gray-300 rounded"
                                >
                                <span class="ml-2 text-sm text-gray-700">Subscribe to newsletter updates</span>
                            </label>
                            <label class="flex items-center">
                                <input 
                                    v-model="profileForm.email_notifications"
                                    type="checkbox"
                                    class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-gray-300 rounded"
                                >
                                <span class="ml-2 text-sm text-gray-700">Receive email notifications</span>
                            </label>
                            <label class="flex items-center">
                                <input 
                                    v-model="profileForm.sms_notifications"
                                    type="checkbox"
                                    class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-gray-300 rounded"
                                >
                                <span class="ml-2 text-sm text-gray-700">Receive SMS notifications</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button 
                        type="submit"
                        :disabled="profileForm.processing"
                        class="inline-flex items-center px-4 py-2 bg-sky-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 focus:bg-sky-700 active:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                    >
                        <svg v-if="profileForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ profileForm.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>

            <!-- Change Password Form -->
            <form @submit.prevent="updatePassword" class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Change Password</h3>
                </div>
                <div class="px-6 py-6 space-y-6">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                        <input 
                            id="current_password"
                            v-model="passwordForm.current_password"
                            type="password"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                            required
                        >
                        <div v-if="passwordForm.errors.current_password" class="mt-2 text-sm text-red-600">{{ passwordForm.errors.current_password }}</div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
                        <input 
                            id="password"
                            v-model="passwordForm.password"
                            type="password"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                            required
                        >
                        <div v-if="passwordForm.errors.password" class="mt-2 text-sm text-red-600">{{ passwordForm.errors.password }}</div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                        <input 
                            id="password_confirmation"
                            v-model="passwordForm.password_confirmation"
                            type="password"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                            required
                        >
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button 
                        type="submit"
                        :disabled="passwordForm.processing"
                        class="inline-flex items-center px-4 py-2 bg-sky-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 focus:bg-sky-700 active:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                    >
                        <svg v-if="passwordForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ passwordForm.processing ? 'Updating...' : 'Update Password' }}
                    </button>
                </div>
            </form>
        </div>
    </AdminLayout>
</template>