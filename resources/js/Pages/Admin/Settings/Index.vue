<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    settings: Object,
    counties: Array,
});

const activeTab = ref('organization');

// Form for organization information
const organizationForm = useForm({
    organization_name: props.settings?.organization_name || 'JS Bloom Foundation',
    organization_description: props.settings?.organization_description || '',
    contact_email: props.settings?.contact_email || '',
    contact_phone: props.settings?.contact_phone || '',
    physical_address: props.settings?.physical_address || '',
    mission_statement: props.settings?.mission_statement || '',
});

// Form for donation settings
const donationForm = useForm({
    mpesa_paybill: props.settings?.mpesa_paybill || '',
    default_amounts: props.settings?.default_amounts || [500, 1000, 2500, 5000],
    receipt_email_template: props.settings?.receipt_email_template || '',
});

// Form for email settings
const emailForm = useForm({
    from_email: props.settings?.from_email || '',
    contact_form_recipient: props.settings?.contact_form_recipient || '',
    admin_email: props.settings?.admin_email || '',
    support_email: props.settings?.support_email || '',
    donations_email: props.settings?.donations_email || '',
    partnerships_email: props.settings?.partnerships_email || '',
    email_signature: props.settings?.email_signature || '',
    smtp_host: props.settings?.smtp_host || '',
    smtp_port: props.settings?.smtp_port || 587,
    smtp_username: props.settings?.smtp_username || '',
    smtp_password: props.settings?.smtp_password || '',
    smtp_encryption: props.settings?.smtp_encryption || 'tls',
});

// Form for social media
const socialForm = useForm({
    facebook_url: props.settings?.facebook_url || '',
    twitter_url: props.settings?.twitter_url || '',
    instagram_url: props.settings?.instagram_url || '',
    linkedin_url: props.settings?.linkedin_url || '',
    youtube_url: props.settings?.youtube_url || '',
    tiktok_url: props.settings?.tiktok_url || '',
    whatsapp_url: props.settings?.whatsapp_url || '',
});

// Form for homepage images
const homepageForm = useForm({
    images: [],
});

// Homepage images management
const homepageImages = ref([]);
const selectedFiles = ref([]);
const currentPreviewIndex = ref(0);
const uploadMessage = ref('');
const showUploadMessage = ref(false);

// Initialize homepage images properly
if (props.settings?.homepage_images && Array.isArray(props.settings.homepage_images)) {
    homepageImages.value = props.settings.homepage_images.map(img => ({
        ...img,
        isNew: false
    }));
}

const handleImageUpload = (event) => {
    let files = Array.from(event.target.files);
    
    // Check if adding these files would exceed the limit
    const currentCount = homepageImages.value.length;
    const newCount = files.length;
    const totalCount = currentCount + newCount;
    
    if (currentCount >= 4) {
        uploadMessage.value = 'Maximum 4 images allowed. Please delete an existing image first.';
        showUploadMessage.value = true;
        setTimeout(() => {
            showUploadMessage.value = false;
        }, 3000);
        event.target.value = ''; // Reset input
        return;
    }
    
    if (totalCount > 4) {
        const allowedCount = 4 - currentCount;
        uploadMessage.value = `You can only add ${allowedCount} more image(s). Selected ${newCount} files. Only first ${allowedCount} will be used.`;
        showUploadMessage.value = true;
        setTimeout(() => {
            showUploadMessage.value = false;
        }, 3000);
        files = files.slice(0, allowedCount);
    }
    
    // Validate file types and sizes
    const validFiles = [];
    const maxSize = 20 * 1024 * 1024; // 20MB
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    
    files.forEach(file => {
        if (!allowedTypes.includes(file.type)) {
            uploadMessage.value = `File ${file.name} is not a valid image type. Only JPG, JPEG, and PNG are allowed.`;
            showUploadMessage.value = true;
            return;
        }
        if (file.size > maxSize) {
            uploadMessage.value = `File ${file.name} exceeds 20MB size limit.`;
            showUploadMessage.value = true;
            return;
        }
        validFiles.push(file);
    });
    
    if (validFiles.length === 0) {
        event.target.value = ''; // Reset input
        return;
    }
    
    selectedFiles.value = validFiles;
    
    // Preview selected images
    validFiles.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            homepageImages.value.push({
                id: Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                file: file,
                filename: file.name,
                preview: e.target.result,
                isNew: true
            });
        };
        reader.readAsDataURL(file);
    });
    
    // Reset input
    event.target.value = '';
};

const removeImage = (imageId) => {
    // Find the image being removed
    const imageToRemove = homepageImages.value.find(img => img.id === imageId);
    
    if (imageToRemove && !imageToRemove.isNew) {
        // This is an existing image from database, need to save the deletion
        homepageImages.value = homepageImages.value.filter(img => img.id !== imageId);
        
        // Reset preview index if it's out of bounds
        if (currentPreviewIndex.value >= homepageImages.value.length) {
            currentPreviewIndex.value = Math.max(0, homepageImages.value.length - 1);
        }
        
        // Immediately save the deletion to database
        const formData = new FormData();
        formData.append('_method', 'PUT');
        
        // Only keep the remaining images
        const existingImages = homepageImages.value
            .filter(img => !img.isNew)
            .map(img => img.id);
        
        formData.append('existing_images', JSON.stringify(existingImages));
        
        // Save to database
        router.post(route('admin.settings.update', 'homepage'), formData, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: (page) => {
                // Update from server response
                if (page.props.settings?.homepage_images) {
                    homepageImages.value = page.props.settings.homepage_images.map(img => ({
                        ...img,
                        isNew: false
                    }));
                }
                
                uploadMessage.value = 'Image deleted successfully!';
                showUploadMessage.value = true;
                setTimeout(() => {
                    showUploadMessage.value = false;
                }, 2000);
            },
            onError: () => {
                // Restore the image on error
                location.reload();
            }
        });
    } else {
        // Just remove from local state if it's a new image not yet saved
        homepageImages.value = homepageImages.value.filter(img => img.id !== imageId);
        
        // Reset preview index if it's out of bounds
        if (currentPreviewIndex.value >= homepageImages.value.length) {
            currentPreviewIndex.value = Math.max(0, homepageImages.value.length - 1);
        }
    }
};

const uploadImages = () => {
    const formData = new FormData();
    
    // Add method override for PUT
    formData.append('_method', 'PUT');
    
    // Add new image files
    let imageIndex = 0;
    homepageImages.value.forEach((image) => {
        if (image.isNew && image.file) {
            formData.append(`images[${imageIndex}]`, image.file);
            imageIndex++;
        }
    });
    
    // Add existing image IDs to keep
    const existingImages = homepageImages.value
        .filter(img => !img.isNew)
        .map(img => img.id);
    
    formData.append('existing_images', JSON.stringify(existingImages));
    
    // Use router.post with FormData
    router.post(route('admin.settings.update', 'homepage'), formData, {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: (page) => {
            // Update homepage images from response
            if (page.props.settings?.homepage_images) {
                homepageImages.value = page.props.settings.homepage_images.map(img => ({
                    ...img,
                    isNew: false
                }));
            } else {
                // Reset new image flags
                homepageImages.value = homepageImages.value.map(img => ({
                    ...img,
                    isNew: false
                }));
            }
            selectedFiles.value = [];
            
            // Show success message
            uploadMessage.value = 'Images uploaded successfully!';
            showUploadMessage.value = true;
            setTimeout(() => {
                showUploadMessage.value = false;
            }, 3000);
        },
        onError: (errors) => {
            console.error('Upload errors:', errors);
            if (errors.message && errors.message.includes('419')) {
                alert('Your session has expired. The page will refresh.');
                window.location.reload();
            } else if (errors.images) {
                uploadMessage.value = errors.images;
                showUploadMessage.value = true;
                setTimeout(() => {
                    showUploadMessage.value = false;
                }, 5000);
            } else {
                uploadMessage.value = 'Error uploading images. Please try again.';
                showUploadMessage.value = true;
                setTimeout(() => {
                    showUploadMessage.value = false;
                }, 5000);
            }
        }
    });
};

const clearAllImages = () => {
    if (confirm('Are you sure you want to remove all images? This action cannot be undone.')) {
        // Store original images for potential restore
        const originalImages = [...homepageImages.value];
        
        // Clear local state
        homepageImages.value = [];
        currentPreviewIndex.value = 0;
        
        // Immediately save empty array to database
        const formData = new FormData();
        formData.append('_method', 'PUT');
        formData.append('existing_images', JSON.stringify([]));
        
        router.post(route('admin.settings.update', 'homepage'), formData, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                uploadMessage.value = 'All images removed successfully!';
                showUploadMessage.value = true;
                setTimeout(() => {
                    showUploadMessage.value = false;
                }, 2000);
            },
            onError: () => {
                // Restore images on error
                homepageImages.value = originalImages;
                uploadMessage.value = 'Error removing images. Please try again.';
                showUploadMessage.value = true;
                setTimeout(() => {
                    showUploadMessage.value = false;
                }, 3000);
            }
        });
    }
};


const updateOrganization = () => {
    organizationForm.put(route('admin.settings.update', 'organization'), {
        onError: (errors) => {
            if (errors.message && errors.message.includes('419')) {
                alert('Your session has expired. The page will refresh.');
                window.location.reload();
            }
        },
        preserveScroll: true,
    });
};

const updateDonations = () => {
    donationForm.put(route('admin.settings.update', 'donations'), {
        onError: (errors) => {
            if (errors.message && errors.message.includes('419')) {
                alert('Your session has expired. The page will refresh.');
                window.location.reload();
            }
        },
        preserveScroll: true,
    });
};

const updateEmail = () => {
    emailForm.put(route('admin.settings.update', 'email'), {
        onError: (errors) => {
            if (errors.message && errors.message.includes('419')) {
                alert('Your session has expired. The page will refresh.');
                window.location.reload();
            }
        },
        preserveScroll: true,
    });
};

const updateSocial = () => {
    socialForm.put(route('admin.settings.update', 'social'), {
        onError: (errors) => {
            if (errors.message && errors.message.includes('419')) {
                alert('Your session has expired. The page will refresh.');
                window.location.reload();
            }
        },
        preserveScroll: true,
    });
};

const updateHomepage = () => {
    uploadImages();
};


const addDefaultAmount = () => {
    donationForm.default_amounts.push(0);
};

const removeDefaultAmount = (index) => {
    donationForm.default_amounts.splice(index, 1);
};

const tabs = [
    { id: 'organization', name: 'Organization', icon: 'M2.25 21h19.5m-18-18v18m0-3.5h.008v.008H3.75V16.5zm0-3.5h.008v.008H3.75V13zm0-3.5h.008v.008H3.75V9.5zm0-3.5h.008v.008H3.75V6zm0-3.5h.008v.008H3.75V2.5zm6.75 18v-18m0 3.5h.008v.008h-.008V5.5zm0 3.5h.008v.008h-.008V9zm0 3.5h.008v.008h-.008v-.008zm0 3.5h.008v.008h-.008V16zm6.75 3v-18m0 3.5h.008v.008h-.008V5.5zm0 3.5h.008v.008h-.008V9zm0 3.5h.008v.008h-.008v-.008zm0 3.5h.008v.008h-.008V16zm6.75 3v-18m0 3.5h.008v.008H20.25V5.5zm0 3.5h.008v.008H20.25V9zm0 3.5h.008v.008H20.25v-.008zm0 3.5h.008v.008H20.25V16z' },
    { id: 'donations', name: 'Donations', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { id: 'homepage', name: 'Homepage', icon: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25' },
];
</script>

<template>
    <Head title="Site Settings" />

    <AdminLayout>
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Site Settings</h1>
                <p class="text-gray-600">Configure your organization's website settings and preferences</p>
            </div>
        </template>

        <div class="max-w-6xl mx-auto">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-8">
                <nav class="-mb-px flex space-x-8">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            activeTab === tab.id
                                ? 'border-sky-500 text-sky-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm flex items-center'
                        ]"
                    >
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon" />
                        </svg>
                        {{ tab.name }}
                    </button>
                </nav>
            </div>

            <!-- Organization Information Tab -->
            <div v-if="activeTab === 'organization'" class="space-y-6">
                <form @submit.prevent="updateOrganization" class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Organization Information</h3>
                        <p class="text-sm text-gray-600">Basic information about your organization</p>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div>
                            <label for="organization_name" class="block text-sm font-medium text-gray-700">Organization Name</label>
                            <input 
                                id="organization_name"
                                v-model="organizationForm.organization_name"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                required
                            >
                            <div v-if="organizationForm.errors.organization_name" class="mt-2 text-sm text-red-600">{{ organizationForm.errors.organization_name }}</div>
                        </div>

                        <div>
                            <label for="organization_description" class="block text-sm font-medium text-gray-700">Organization Description</label>
                            <textarea 
                                id="organization_description"
                                v-model="organizationForm.organization_description"
                                rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                placeholder="Brief description of your organization..."
                            ></textarea>
                            <div v-if="organizationForm.errors.organization_description" class="mt-2 text-sm text-red-600">{{ organizationForm.errors.organization_description }}</div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="contact_email" class="block text-sm font-medium text-gray-700">Contact Email</label>
                                <input 
                                    id="contact_email"
                                    v-model="organizationForm.contact_email"
                                    type="email"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                    required
                                >
                                <div v-if="organizationForm.errors.contact_email" class="mt-2 text-sm text-red-600">{{ organizationForm.errors.contact_email }}</div>
                            </div>

                            <div>
                                <label for="contact_phone" class="block text-sm font-medium text-gray-700">Contact Phone</label>
                                <input 
                                    id="contact_phone"
                                    v-model="organizationForm.contact_phone"
                                    type="tel"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                    placeholder="+254 700 000 000"
                                >
                                <div v-if="organizationForm.errors.contact_phone" class="mt-2 text-sm text-red-600">{{ organizationForm.errors.contact_phone }}</div>
                            </div>
                        </div>

                        <div>
                            <label for="physical_address" class="block text-sm font-medium text-gray-700">Physical Address</label>
                            <textarea 
                                id="physical_address"
                                v-model="organizationForm.physical_address"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                placeholder="Organization's physical address..."
                            ></textarea>
                            <div v-if="organizationForm.errors.physical_address" class="mt-2 text-sm text-red-600">{{ organizationForm.errors.physical_address }}</div>
                        </div>

                        <div>
                            <label for="mission_statement" class="block text-sm font-medium text-gray-700">Mission Statement</label>
                            <textarea 
                                id="mission_statement"
                                v-model="organizationForm.mission_statement"
                                rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                placeholder="Your organization's mission statement..."
                            ></textarea>
                            <div v-if="organizationForm.errors.mission_statement" class="mt-2 text-sm text-red-600">{{ organizationForm.errors.mission_statement }}</div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                        <button 
                            type="submit"
                            :disabled="organizationForm.processing"
                            class="inline-flex items-center px-4 py-2 bg-sky-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 focus:bg-sky-700 active:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                        >
                            {{ organizationForm.processing ? 'Saving...' : 'Save Organization Info' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Donation Settings Tab -->
            <div v-if="activeTab === 'donations'" class="space-y-6">
                <form @submit.prevent="updateDonations" class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Donation Settings</h3>
                        <p class="text-sm text-gray-600">Configure payment methods and donation preferences</p>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div>
                            <label for="mpesa_paybill" class="block text-sm font-medium text-gray-700">M-Pesa Paybill Number</label>
                            <input 
                                id="mpesa_paybill"
                                v-model="donationForm.mpesa_paybill"
                                type="text"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                placeholder="000000"
                            >
                            <p class="mt-1 text-sm text-gray-500">Your organization's M-Pesa Paybill number for donations</p>
                            <div v-if="donationForm.errors.mpesa_paybill" class="mt-2 text-sm text-red-600">{{ donationForm.errors.mpesa_paybill }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Default Donation Amounts (KES)</label>
                            <p class="text-sm text-gray-500 mb-3">Preset amounts that donors can quickly select</p>
                            <div class="space-y-2">
                                <div v-for="(amount, index) in donationForm.default_amounts" :key="index" class="flex items-center space-x-2">
                                    <input 
                                        v-model.number="donationForm.default_amounts[index]"
                                        type="number"
                                        min="1"
                                        class="block w-32 border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                        placeholder="Amount"
                                    >
                                    <button 
                                        v-if="donationForm.default_amounts.length > 1"
                                        type="button"
                                        @click="removeDefaultAmount(index)"
                                        class="text-red-600 hover:text-red-700"
                                    >
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                                <button 
                                    type="button"
                                    @click="addDefaultAmount"
                                    class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500"
                                >
                                    <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Add Amount
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="receipt_email_template" class="block text-sm font-medium text-gray-700">Donation Receipt Email Template</label>
                            <textarea
                                id="receipt_email_template"
                                v-model="donationForm.receipt_email_template"
                                rows="6"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                placeholder="Dear {donor_name},&#10;&#10;Thank you for your generous donation of NGN {amount} to JS Bloom Foundation.&#10;&#10;Your support helps us continue our mission...&#10;&#10;Best regards,&#10;JS Bloom Foundation Team"
                            ></textarea>
                            <p class="mt-1 text-sm text-gray-500">Use {donor_name}, {amount}, {date} as placeholders</p>
                            <div v-if="donationForm.errors.receipt_email_template" class="mt-2 text-sm text-red-600">{{ donationForm.errors.receipt_email_template }}</div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                        <button 
                            type="submit"
                            :disabled="donationForm.processing"
                            class="inline-flex items-center px-4 py-2 bg-sky-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 focus:bg-sky-700 active:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                        >
                            {{ donationForm.processing ? 'Saving...' : 'Save Donation Settings' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Email Settings Tab (Removed) -->
            <div v-if="false" class="space-y-6">
                <form @submit.prevent="updateEmail" class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Email Configuration</h3>
                        <p class="text-sm text-gray-600">Configure email addresses and SMTP settings for your organization</p>
                    </div>
                    <div class="px-6 py-6 space-y-8">
                        <!-- Email Addresses Section -->
                        <div>
                            <h4 class="text-md font-semibold text-gray-900 mb-4">Organization Email Addresses</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- From Email -->
                                <div>
                                    <label for="from_email" class="block text-sm font-medium text-gray-700">
                                        <div class="flex items-center">
                                            <svg class="mr-2 h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                            </svg>
                                            From Email Address
                                        </div>
                                    </label>
                                    <input
                                        id="from_email"
                                        v-model="emailForm.from_email"
                                        type="email"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                        placeholder="noreply@jsbloomfoundation.org"
                                        required
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Default sender email for system notifications</p>
                                    <div v-if="emailForm.errors.from_email" class="mt-2 text-sm text-red-600">{{ emailForm.errors.from_email }}</div>
                                </div>

                                <!-- Admin Email -->
                                <div>
                                    <label for="admin_email" class="block text-sm font-medium text-gray-700">
                                        <div class="flex items-center">
                                            <svg class="mr-2 h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                            </svg>
                                            Admin Email
                                        </div>
                                    </label>
                                    <input
                                        id="admin_email"
                                        v-model="emailForm.admin_email"
                                        type="email"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                        placeholder="admin@jsbloomfoundation.org"
                                        required
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Admin notifications and system alerts</p>
                                    <div v-if="emailForm.errors.admin_email" class="mt-2 text-sm text-red-600">{{ emailForm.errors.admin_email }}</div>
                                </div>

                                <!-- Contact Form Recipient -->
                                <div>
                                    <label for="contact_form_recipient" class="block text-sm font-medium text-gray-700">
                                        <div class="flex items-center">
                                            <svg class="mr-2 h-4 w-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                            </svg>
                                            Contact Form Email
                                        </div>
                                    </label>
                                    <input
                                        id="contact_form_recipient"
                                        v-model="emailForm.contact_form_recipient"
                                        type="email"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                        placeholder="info@jsbloomfoundation.org"
                                        required
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Receives website contact form submissions</p>
                                    <div v-if="emailForm.errors.contact_form_recipient" class="mt-2 text-sm text-red-600">{{ emailForm.errors.contact_form_recipient }}</div>
                                </div>

                                <!-- Support Email -->
                                <div>
                                    <label for="support_email" class="block text-sm font-medium text-gray-700">
                                        <div class="flex items-center">
                                            <svg class="mr-2 h-4 w-4 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            Support Email
                                        </div>
                                    </label>
                                    <input
                                        id="support_email"
                                        v-model="emailForm.support_email"
                                        type="email"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                        placeholder="support@jsbloomfoundation.org"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Technical support and user assistance</p>
                                    <div v-if="emailForm.errors.support_email" class="mt-2 text-sm text-red-600">{{ emailForm.errors.support_email }}</div>
                                </div>

                                <!-- Donations Email -->
                                <div>
                                    <label for="donations_email" class="block text-sm font-medium text-gray-700">
                                        <div class="flex items-center">
                                            <svg class="mr-2 h-4 w-4 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Donations Email
                                        </div>
                                    </label>
                                    <input
                                        id="donations_email"
                                        v-model="emailForm.donations_email"
                                        type="email"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                        placeholder="donations@jsbloomfoundation.org"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Donation receipts and donor communications</p>
                                    <div v-if="emailForm.errors.donations_email" class="mt-2 text-sm text-red-600">{{ emailForm.errors.donations_email }}</div>
                                </div>

                                <!-- Partnerships Email -->
                                <div>
                                    <label for="partnerships_email" class="block text-sm font-medium text-gray-700">
                                        <div class="flex items-center">
                                            <svg class="mr-2 h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            Partnerships Email
                                        </div>
                                    </label>
                                    <input
                                        id="partnerships_email"
                                        v-model="emailForm.partnerships_email"
                                        type="email"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                        placeholder="partnerships@jsbloomfoundation.org"
                                    >
                                    <p class="mt-1 text-xs text-gray-500">Partnership inquiries and collaborations</p>
                                    <div v-if="emailForm.errors.partnerships_email" class="mt-2 text-sm text-red-600">{{ emailForm.errors.partnerships_email }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Email Signature Section -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-4">Email Signature</h4>
                            <div>
                                <label for="email_signature" class="block text-sm font-medium text-gray-700">Default Email Signature</label>
                                <textarea
                                    id="email_signature"
                                    v-model="emailForm.email_signature"
                                    rows="4"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                    placeholder="Best regards,&#10;JS Bloom Foundation Team&#10;Email: info@jsbloomfoundation.org&#10;Website: www.jsbloomfoundation.org"
                                ></textarea>
                                <p class="mt-1 text-sm text-gray-500">This signature will be automatically added to system emails</p>
                                <div v-if="emailForm.errors.email_signature" class="mt-2 text-sm text-red-600">{{ emailForm.errors.email_signature }}</div>
                            </div>
                        </div>

                        <!-- SMTP Configuration Section -->
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-md font-semibold text-gray-900 mb-4">SMTP Configuration</h4>
                            <p class="text-sm text-gray-600 mb-4">Configure SMTP settings for sending emails (optional - uses default if empty)</p>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- SMTP Host -->
                                <div>
                                    <label for="smtp_host" class="block text-sm font-medium text-gray-700">SMTP Host</label>
                                    <input 
                                        id="smtp_host"
                                        v-model="emailForm.smtp_host"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                        placeholder="smtp.gmail.com"
                                    >
                                    <div v-if="emailForm.errors.smtp_host" class="mt-2 text-sm text-red-600">{{ emailForm.errors.smtp_host }}</div>
                                </div>

                                <!-- SMTP Port -->
                                <div>
                                    <label for="smtp_port" class="block text-sm font-medium text-gray-700">SMTP Port</label>
                                    <input 
                                        id="smtp_port"
                                        v-model.number="emailForm.smtp_port"
                                        type="number"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                        placeholder="587"
                                    >
                                    <div v-if="emailForm.errors.smtp_port" class="mt-2 text-sm text-red-600">{{ emailForm.errors.smtp_port }}</div>
                                </div>

                                <!-- SMTP Username -->
                                <div>
                                    <label for="smtp_username" class="block text-sm font-medium text-gray-700">SMTP Username</label>
                                    <input 
                                        id="smtp_username"
                                        v-model="emailForm.smtp_username"
                                        type="text"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                        placeholder="your-email@gmail.com"
                                    >
                                    <div v-if="emailForm.errors.smtp_username" class="mt-2 text-sm text-red-600">{{ emailForm.errors.smtp_username }}</div>
                                </div>

                                <!-- SMTP Password -->
                                <div>
                                    <label for="smtp_password" class="block text-sm font-medium text-gray-700">SMTP Password</label>
                                    <input 
                                        id="smtp_password"
                                        v-model="emailForm.smtp_password"
                                        type="password"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                        placeholder="••••••••••••"
                                    >
                                    <div v-if="emailForm.errors.smtp_password" class="mt-2 text-sm text-red-600">{{ emailForm.errors.smtp_password }}</div>
                                </div>

                                <!-- SMTP Encryption -->
                                <div>
                                    <label for="smtp_encryption" class="block text-sm font-medium text-gray-700">Encryption</label>
                                    <select 
                                        id="smtp_encryption"
                                        v-model="emailForm.smtp_encryption"
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                    >
                                        <option value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                        <option value="none">None</option>
                                    </select>
                                    <div v-if="emailForm.errors.smtp_encryption" class="mt-2 text-sm text-red-600">{{ emailForm.errors.smtp_encryption }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                        <button 
                            type="submit"
                            :disabled="emailForm.processing"
                            class="inline-flex items-center px-4 py-2 bg-sky-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 focus:bg-sky-700 active:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                        >
                            {{ emailForm.processing ? 'Saving...' : 'Save Email Settings' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Social Media Settings Tab (Removed) -->
            <div v-if="false" class="space-y-6">
                <form @submit.prevent="updateSocial" class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Social Media Links</h3>
                        <p class="text-sm text-gray-600">Configure your organization's social media presence</p>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Facebook -->
                            <div>
                                <label for="facebook_url" class="block text-sm font-medium text-gray-700">
                                    <div class="flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                        </svg>
                                        Facebook Page URL
                                    </div>
                                </label>
                                <input
                                    id="facebook_url"
                                    v-model="socialForm.facebook_url"
                                    type="url"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                    placeholder="https://facebook.com/jsbloomfoundation"
                                >
                                <div v-if="socialForm.errors.facebook_url" class="mt-2 text-sm text-red-600">{{ socialForm.errors.facebook_url }}</div>
                            </div>

                            <!-- Instagram -->
                            <div>
                                <label for="instagram_url" class="block text-sm font-medium text-gray-700">
                                    <div class="flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.618 5.367 11.986 11.988 11.986s11.987-5.368 11.987-11.986C24.014 5.367 18.635.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.49-3.323-1.297C4.198 14.895 3.652 13.455 3.652 12c0-1.297.49-2.448 1.297-3.323C5.848 7.8 7.288 7.254 8.449 7.254c1.297 0 2.448.49 3.323 1.297.875.807 1.421 2.247 1.421 3.324 0 1.297-.49 2.448-1.297 3.323-.875.875-2.315 1.421-3.324 1.421zm8.555-8.772h-1.718v1.718h1.718V8.216zm-4.988 1.604c-.736 0-1.297.561-1.297 1.297s.561 1.297 1.297 1.297 1.297-.561 1.297-1.297-.561-1.297-1.297-1.297z"/>
                                        </svg>
                                        Instagram Profile URL
                                    </div>
                                </label>
                                <input
                                    id="instagram_url"
                                    v-model="socialForm.instagram_url"
                                    type="url"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                    placeholder="https://instagram.com/jsbloomfoundation"
                                >
                                <div v-if="socialForm.errors.instagram_url" class="mt-2 text-sm text-red-600">{{ socialForm.errors.instagram_url }}</div>
                            </div>

                            <!-- Twitter/X -->
                            <div>
                                <label for="twitter_url" class="block text-sm font-medium text-gray-700">
                                    <div class="flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-gray-900" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                        </svg>
                                        Twitter/X Profile URL
                                    </div>
                                </label>
                                <input
                                    id="twitter_url"
                                    v-model="socialForm.twitter_url"
                                    type="url"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                    placeholder="https://twitter.com/jsbloomfoundation"
                                >
                                <div v-if="socialForm.errors.twitter_url" class="mt-2 text-sm text-red-600">{{ socialForm.errors.twitter_url }}</div>
                            </div>

                            <!-- LinkedIn -->
                            <div>
                                <label for="linkedin_url" class="block text-sm font-medium text-gray-700">
                                    <div class="flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-blue-700" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                        </svg>
                                        LinkedIn Company URL
                                    </div>
                                </label>
                                <input
                                    id="linkedin_url"
                                    v-model="socialForm.linkedin_url"
                                    type="url"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                    placeholder="https://linkedin.com/company/js-bloom-foundation"
                                >
                                <div v-if="socialForm.errors.linkedin_url" class="mt-2 text-sm text-red-600">{{ socialForm.errors.linkedin_url }}</div>
                            </div>

                            <!-- YouTube -->
                            <div>
                                <label for="youtube_url" class="block text-sm font-medium text-gray-700">
                                    <div class="flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                        </svg>
                                        YouTube Channel URL
                                    </div>
                                </label>
                                <input
                                    id="youtube_url"
                                    v-model="socialForm.youtube_url"
                                    type="url"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                    placeholder="https://youtube.com/@jsbloomfoundation"
                                >
                                <div v-if="socialForm.errors.youtube_url" class="mt-2 text-sm text-red-600">{{ socialForm.errors.youtube_url }}</div>
                            </div>

                            <!-- TikTok -->
                            <div>
                                <label for="tiktok_url" class="block text-sm font-medium text-gray-700">
                                    <div class="flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-black" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                        </svg>
                                        TikTok Profile URL
                                    </div>
                                </label>
                                <input
                                    id="tiktok_url"
                                    v-model="socialForm.tiktok_url"
                                    type="url"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                    placeholder="https://tiktok.com/@jsbloomfoundation"
                                >
                                <div v-if="socialForm.errors.tiktok_url" class="mt-2 text-sm text-red-600">{{ socialForm.errors.tiktok_url }}</div>
                            </div>

                            <!-- WhatsApp -->
                            <div>
                                <label for="whatsapp_url" class="block text-sm font-medium text-gray-700">
                                    <div class="flex items-center">
                                        <svg class="mr-2 h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                                        </svg>
                                        WhatsApp Business URL
                                    </div>
                                </label>
                                <input 
                                    id="whatsapp_url"
                                    v-model="socialForm.whatsapp_url"
                                    type="url"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-500 focus:border-sky-500 sm:text-sm"
                                    placeholder="https://wa.me/254700000000"
                                >
                                <div v-if="socialForm.errors.whatsapp_url" class="mt-2 text-sm text-red-600">{{ socialForm.errors.whatsapp_url }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                        <button 
                            type="submit"
                            :disabled="socialForm.processing"
                            class="inline-flex items-center px-4 py-2 bg-sky-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 focus:bg-sky-700 active:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                        >
                            {{ socialForm.processing ? 'Saving...' : 'Save Social Media Settings' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Homepage Images Tab -->
            <div v-if="activeTab === 'homepage'" class="space-y-6">
                <form @submit.prevent="updateHomepage" class="bg-white shadow-sm rounded-lg border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Homepage Images</h3>
                        <p class="text-sm text-gray-600">Upload and manage images for your homepage slider</p>
                    </div>
                    <div class="px-6 py-6 space-y-6">
                        <!-- Success/Error Message -->
                        <transition
                            enter-active-class="transform ease-out duration-300 transition"
                            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
                            leave-active-class="transition ease-in duration-100"
                            leave-from-class="opacity-100"
                            leave-to-class="opacity-0"
                        >
                            <div v-if="showUploadMessage" class="rounded-md p-4 border" :class="uploadMessage.includes('success') || uploadMessage.includes('successfully') ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg v-if="uploadMessage.includes('success') || uploadMessage.includes('successfully')" class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <svg v-else class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium" :class="uploadMessage.includes('success') || uploadMessage.includes('successfully') ? 'text-green-800' : 'text-red-800'">
                                            {{ uploadMessage }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </transition>

                        <!-- Image Upload Section -->
                        <div class="space-y-4">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-gray-700">
                                        Upload New Images
                                    </label>
                                    <span class="text-sm" :class="homepageImages.length >= 4 ? 'text-red-600 font-medium' : 'text-gray-500'">
                                        {{ homepageImages.length }}/4 images
                                    </span>
                                </div>
                                <div class="flex items-center justify-center w-full">
                                    <label 
                                        for="homepage-images" 
                                        :class="[
                                            'flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-lg transition-colors',
                                            homepageImages.length >= 4 
                                                ? 'border-gray-200 bg-gray-50 cursor-not-allowed' 
                                                : 'border-gray-300 cursor-pointer bg-gray-50 hover:bg-gray-100'
                                        ]"
                                    >
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg class="w-8 h-8 mb-4" :class="homepageImages.length >= 4 ? 'text-gray-300' : 'text-gray-500'" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                            </svg>
                                            <p class="mb-2 text-sm" :class="homepageImages.length >= 4 ? 'text-gray-400' : 'text-gray-500'">
                                                <span v-if="homepageImages.length >= 4">Maximum 4 images reached</span>
                                                <span v-else><span class="font-semibold">Click to upload</span> or drag and drop</span>
                                            </p>
                                            <p class="text-xs" :class="homepageImages.length >= 4 ? 'text-gray-400' : 'text-gray-500'">
                                                <span v-if="homepageImages.length >= 4">Delete an existing image to upload new ones</span>
                                                <span v-else>PNG, JPG, JPEG (Max. 20MB each)</span>
                                            </p>
                                        </div>
                                        <input 
                                            id="homepage-images" 
                                            type="file" 
                                            class="hidden" 
                                            multiple 
                                            accept="image/*"
                                            :disabled="homepageImages.length >= 4"
                                            @change="handleImageUpload"
                                        />
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Current Images Grid -->
                        <div v-if="homepageImages.length > 0" class="space-y-4">
                            <h4 class="text-sm font-medium text-gray-700">Current Homepage Images</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                <div 
                                    v-for="image in homepageImages" 
                                    :key="image.id"
                                    class="relative group"
                                >
                                    <div class="aspect-w-16 aspect-h-9 w-full overflow-hidden rounded-lg bg-gray-100">
                                        <img 
                                            :src="image.preview || image.url || `/storage/${image.path || 'homepage/' + image.filename}`" 
                                            :alt="`Homepage image ${image.id}`"
                                            class="w-full h-32 object-cover rounded-lg"
                                        >
                                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 rounded-lg flex items-center justify-center">
                                            <button
                                                type="button"
                                                @click="removeImage(image.id)"
                                                class="opacity-0 group-hover:opacity-100 bg-red-600 text-white p-2 rounded-full hover:bg-red-700 transition-all duration-200"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mt-2">
                                        <p class="text-xs text-gray-500 truncate">
                                            {{ image.filename || 'New image' }}
                                        </p>
                                        <span v-if="image.isNew" class="inline-flex items-center px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                            New
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Homepage Preview -->
                        <div v-if="homepageImages.length > 0" class="space-y-4">
                            <h4 class="text-sm font-medium text-gray-700">Homepage Preview</h4>
                            <div class="bg-gray-900 rounded-lg overflow-hidden">
                                <div class="relative aspect-w-16 aspect-h-9">
                                    <img 
                                        v-if="homepageImages[currentPreviewIndex]"
                                        :src="homepageImages[currentPreviewIndex].preview || homepageImages[currentPreviewIndex].url || `/storage/${homepageImages[currentPreviewIndex].path || 'homepage/' + homepageImages[currentPreviewIndex].filename}`" 
                                        alt="Homepage preview"
                                        class="w-full h-64 md:h-96 object-cover"
                                    >
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                    <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8">
                                        <h2 class="text-white text-2xl md:text-4xl font-bold mb-2">{{ organizationForm.organization_name }}</h2>
                                        <p class="text-white/90 text-sm md:text-base">{{ organizationForm.mission_statement || 'Empowering communities through sustainable development' }}</p>
                                    </div>
                                    <!-- Slider dots indicator -->
                                    <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex space-x-2">
                                        <button 
                                            v-for="(image, index) in homepageImages"
                                            :key="index"
                                            type="button"
                                            :class="[
                                                'w-2 h-2 rounded-full transition-all duration-200',
                                                index === currentPreviewIndex ? 'bg-white w-8' : 'bg-white/50 hover:bg-white/75'
                                            ]"
                                            @click="currentPreviewIndex = index"></button>
                                    </div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 text-center">This is how your images will appear on the homepage slider</p>
                        </div>
                    </div>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                        <button 
                            type="button"
                            @click="clearAllImages"
                            :disabled="homepageImages.length === 0"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Clear All
                        </button>
                        <button 
                            type="submit"
                            :disabled="homepageForm.processing"
                            class="inline-flex items-center px-4 py-2 bg-sky-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-700 focus:bg-sky-700 active:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-25"
                        >
                            {{ homepageForm.processing ? 'Uploading...' : 'Save Homepage Images' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AdminLayout>
</template>