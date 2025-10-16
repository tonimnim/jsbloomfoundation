<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, onUnmounted } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    images: Object,
});

const showUploadModal = ref(false);

const uploadForm = useForm({
    images: []
});

const previewUrls = ref([]);
const fileInputRef = ref(null);

const handleFilesSelect = (event) => {
    const files = Array.from(event.target.files);
    
    // Filter files by size - max 20MB per file
    const maxSize = 20 * 1024 * 1024; // 20MB
    const validFiles = [];
    const oversizedFiles = [];
    
    files.forEach((file) => {
        if (file.size > maxSize) {
            oversizedFiles.push(file);
        } else {
            validFiles.push(file);
        }
    });
    
    if (oversizedFiles.length > 0) {
        const names = oversizedFiles.map(f => `${f.name} (${(f.size / (1024 * 1024)).toFixed(1)}MB)`).join(', ');
        alert(`The following files exceed 20MB and cannot be uploaded: ${names}\n\nPlease resize them before uploading.`);
    }
    
    uploadForm.images = validFiles;
    
    // Clear old preview URLs
    previewUrls.value.forEach(url => URL.revokeObjectURL(url));
    
    // Generate new preview URLs
    previewUrls.value = validFiles.map(file => URL.createObjectURL(file));
};

const removePreviewImage = (index) => {
    // Revoke the object URL to free memory
    URL.revokeObjectURL(previewUrls.value[index]);
    
    // Create new arrays without the removed item
    const newImages = [...uploadForm.images];
    newImages.splice(index, 1);
    uploadForm.images = newImages;
    
    const newPreviews = [...previewUrls.value];
    newPreviews.splice(index, 1);
    previewUrls.value = newPreviews;
    
    // If all images removed, reset the file input
    if (uploadForm.images.length === 0 && fileInputRef.value) {
        fileInputRef.value.value = '';
    }
};

const uploadImages = () => {
    // Check if there are files to upload
    if (!uploadForm.images || uploadForm.images.length === 0) {
        alert('Please select at least one image to upload');
        return;
    }
    
    uploadForm.post('/admin/gallery', {
        forceFormData: true,
        onSuccess: () => {
            showUploadModal.value = false;
            uploadForm.reset();
            previewUrls.value.forEach(url => URL.revokeObjectURL(url));
            previewUrls.value = [];
            if (fileInputRef.value) {
                fileInputRef.value.value = '';
            }
        },
        onError: (errors) => {
            const message = errors.images ? errors.images[0] : 'Upload failed. Please try again.';
            alert(message);
        }
    });
};

const deleteImage = (imageId) => {
    router.delete(`/admin/gallery/${imageId}`);
};

// Clean up object URLs when component unmounts
onUnmounted(() => {
    previewUrls.value.forEach(url => URL.revokeObjectURL(url));
});
</script>

<template>
    <Head title="Gallery Management" />
    
    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Gallery</h1>
                <button 
                    @click="showUploadModal = true"
                    class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200"
                >
                    Upload Images
                </button>
            </div>
        </template>

        <!-- Gallery Grid -->
        <div v-if="images.data && images.data.length > 0" class="bg-white rounded-lg shadow-sm p-4 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-3 sm:gap-4">
                <div 
                    v-for="image in images.data" 
                    :key="image.id"
                    class="group relative aspect-square overflow-hidden rounded-lg bg-gray-100"
                >
                    <img 
                        :src="image.url" 
                        :alt="`Gallery image ${image.id}`"
                        class="w-full h-full object-cover"
                        loading="lazy"
                    />
                    
                    <!-- Hover Overlay with Delete Button -->
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-200 flex items-center justify-center">
                        <button 
                            @click="deleteImage(image.id)"
                            class="opacity-0 group-hover:opacity-100 transition-opacity bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="images.last_page > 1" class="mt-6 flex justify-center">
                <nav class="flex gap-1">
                    <template v-for="link in images.links" :key="link.label">
                        <a
                            v-if="link.url"
                            :href="link.url"
                            @click.prevent="router.get(link.url)"
                            :class="[
                                'px-3 py-2 text-sm font-medium rounded transition-colors duration-200',
                                link.active 
                                    ? 'bg-sky-600 text-white' 
                                    : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300'
                            ]"
                            v-html="link.label"
                        />
                        <span
                            v-else
                            class="px-3 py-2 text-sm font-medium text-gray-400"
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No images yet</h3>
            <p class="text-gray-500 mb-4">Start by uploading your first images.</p>
            <button 
                @click="showUploadModal = true"
                class="inline-flex items-center px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-lg font-medium transition-colors duration-200"
            >
                Upload Images
            </button>
        </div>

        <!-- Upload Modal -->
        <div v-if="showUploadModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Upload Images</h2>
                        <button 
                            @click="showUploadModal = false"
                            class="text-gray-400 hover:text-gray-600"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form @submit.prevent="uploadImages">
                        <!-- File Input -->
                        <div class="mb-4">
                            <label class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-10 h-10 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <p class="mb-2 text-sm text-gray-500">
                                        <span class="font-semibold">Click to select images</span> or drag and drop
                                    </p>
                                    <p class="text-xs text-gray-500">Multiple images allowed (PNG, JPG, GIF, WEBP)</p>
                                </div>
                                <input 
                                    ref="fileInputRef"
                                    type="file" 
                                    class="hidden" 
                                    accept="image/*"
                                    multiple
                                    @change="handleFilesSelect"
                                />
                            </label>
                        </div>

                        <!-- Selected Files Preview -->
                        <div v-if="previewUrls.length > 0" class="mb-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">
                                Selected: {{ uploadForm.images.length }} image(s) - Total: {{ (uploadForm.images.reduce((sum, file) => sum + file.size, 0) / (1024 * 1024)).toFixed(2) }}MB
                            </p>
                            <div class="grid grid-cols-4 gap-2">
                                <div 
                                    v-for="(url, index) in previewUrls.slice(0, 8)" 
                                    :key="index"
                                    class="relative group aspect-square bg-gray-100 rounded overflow-hidden"
                                >
                                    <img 
                                        :src="url" 
                                        :alt="`Preview ${index + 1}`"
                                        class="w-full h-full object-cover"
                                    />
                                    <!-- File size label -->
                                    <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 text-center">
                                        {{ (uploadForm.images[index].size / (1024 * 1024)).toFixed(2) }}MB
                                    </div>
                                    <!-- Delete button for preview -->
                                    <button
                                        type="button"
                                        @click="removePreviewImage(index)"
                                        class="absolute top-1 right-1 bg-red-600 hover:bg-red-700 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                <div v-if="previewUrls.length > 8" class="aspect-square bg-gray-100 rounded flex items-center justify-center">
                                    <span class="text-gray-500 text-sm">+{{ previewUrls.length - 8 }} more</span>
                                </div>
                            </div>
                        </div>

                        <!-- Error Message -->
                        <div v-if="uploadForm.errors.images" class="text-red-500 text-sm mb-4">
                            {{ uploadForm.errors.images }}
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-3">
                            <button
                                type="button"
                                @click="showUploadModal = false"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition-colors duration-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="uploadForm.processing || uploadForm.images.length === 0"
                                :class="[
                                    'px-4 py-2 rounded-lg font-medium transition-colors duration-200',
                                    uploadForm.processing || uploadForm.images.length === 0
                                        ? 'bg-gray-300 text-gray-500 cursor-not-allowed' 
                                        : 'bg-sky-600 hover:bg-sky-700 text-white'
                                ]"
                            >
                                {{ uploadForm.processing ? 'Uploading...' : 'Upload' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>