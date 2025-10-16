<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    show: Boolean,
    existingMedia: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['close', 'select', 'uploaded']);

// Upload state
const uploading = ref(false);
const uploadProgress = ref(0);
const selectedTab = ref('upload'); // 'upload' or 'library'
const selectedMedia = ref([]);
const previewFiles = ref([]);

// File input
const fileInput = ref(null);

// File validation
const validateFile = (file) => {
    const maxSize = 10 * 1024 * 1024; // 10MB
    const allowedTypes = [
        'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
        'video/mp4', 'video/webm', 'video/ogg',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    
    if (file.size > maxSize) {
        return { valid: false, error: `File "${file.name}" is too large. Maximum size is 10MB.` };
    }
    
    if (!allowedTypes.includes(file.type)) {
        return { valid: false, error: `File type "${file.type}" is not allowed.` };
    }
    
    return { valid: true };
};

// Handle file selection
const handleFileSelect = (event) => {
    const files = Array.from(event.target.files || event.dataTransfer?.files || []);
    
    files.forEach(file => {
        const validation = validateFile(file);
        
        if (!validation.valid) {
            alert(validation.error);
            return;
        }
        
        // Check if file already exists in preview
        if (previewFiles.value.some(f => f.name === file.name && f.size === file.size)) {
            alert(`File "${file.name}" is already selected.`);
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            previewFiles.value.push({
                file,
                preview: e.target.result,
                name: file.name,
                size: (file.size / 1024 / 1024).toFixed(2) + ' MB',
                type: file.type,
                id: Date.now() + Math.random() // Unique ID for tracking
            });
        };
        reader.readAsDataURL(file);
    });
    
    // Clear file input
    if (event.target) {
        event.target.value = '';
    }
};

// Remove preview file
const removePreview = (index) => {
    previewFiles.value.splice(index, 1);
};

// Upload files
const uploadFiles = async () => {
    if (previewFiles.value.length === 0) return;
    
    uploading.value = true;
    uploadProgress.value = 0;
    
    const formData = new FormData();
    previewFiles.value.forEach((item, index) => {
        formData.append(`files[${index}]`, item.file);
    });
    
    try {
        const response = await axios.post('/admin/media/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
            onUploadProgress: (progressEvent) => {
                uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
            }
        });
        
        // Check if response has files
        if (response.data && response.data.files && response.data.files.length > 0) {
            // Add uploaded files to selected media
            response.data.files.forEach(file => {
                selectedMedia.value.push(file);
            });
            
            // Clear previews
            previewFiles.value = [];
            
            // Switch to library tab
            selectedTab.value = 'library';
            
            // Show success message
            alert(`${response.data.files.length} file(s) uploaded successfully!`);
            
            // Emit uploaded event to refresh the page
            emit('uploaded', response.data.files);
        } else {
            alert('Upload completed but no files were returned.');
        }
    } catch (error) {
        console.error('Upload error:', error);
        
        if (error.response && error.response.status === 422) {
            const errors = error.response.data.errors;
            const errorMessages = Object.values(errors).flat().join('\n');
            alert('Validation error:\n' + errorMessages);
        } else if (error.response && error.response.status === 419) {
            alert('Session expired. Please refresh the page and try again.');
        } else {
            alert('Error uploading files: ' + (error.response?.data?.message || error.message));
        }
    } finally {
        uploading.value = false;
        uploadProgress.value = 0;
    }
};

// Toggle media selection
const toggleMediaSelection = (media) => {
    const index = selectedMedia.value.findIndex(m => m.id === media.id);
    if (index > -1) {
        selectedMedia.value.splice(index, 1);
    } else {
        selectedMedia.value.push(media);
    }
};

// Check if media is selected
const isSelected = (media) => {
    return selectedMedia.value.some(m => m.id === media.id);
};

// Insert selected media
const insertMedia = async () => {
    if (selectedTab.value === 'upload' && previewFiles.value.length > 0) {
        // Upload files first, then insert them
        await uploadFiles();
        // After upload, selectedMedia will be populated with uploaded files
        emit('select', selectedMedia.value);
    } else {
        // Insert already selected media from library
        emit('select', selectedMedia.value);
    }
    closeModal();
};

// Close modal
const closeModal = () => {
    selectedMedia.value = [];
    previewFiles.value = [];
    selectedTab.value = 'upload';
    emit('close');
};

// File type icon
const getFileIcon = (mimeType) => {
    if (mimeType.startsWith('image/')) return '🖼️';
    if (mimeType.startsWith('video/')) return '🎥';
    if (mimeType.startsWith('audio/')) return '🎵';
    if (mimeType.includes('pdf')) return '📄';
    if (mimeType.includes('word') || mimeType.includes('document')) return '📝';
    if (mimeType.includes('sheet') || mimeType.includes('excel')) return '📊';
    if (mimeType.includes('presentation') || mimeType.includes('powerpoint')) return '📊';
    return '📎';
};

// Trigger file input
const triggerFileInput = () => {
    if (fileInput.value) {
        fileInput.value.click();
    }
};
</script>

<template>
    <transition name="modal">
        <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeModal"></div>
                
                <div class="relative inline-block w-full max-w-6xl overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Media Library</h3>
                        <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Tabs -->
                    <div class="flex border-b border-gray-200">
                        <button
                            @click="selectedTab = 'upload'"
                            :class="[
                                selectedTab === 'upload' ? 'border-sky-500 text-sky-600' : 'border-transparent text-gray-500 hover:text-gray-700',
                                'px-6 py-3 text-sm font-medium border-b-2 transition-colors duration-200'
                            ]"
                        >
                            Upload Files
                        </button>
                        <button
                            @click="selectedTab = 'library'"
                            :class="[
                                selectedTab === 'library' ? 'border-sky-500 text-sky-600' : 'border-transparent text-gray-500 hover:text-gray-700',
                                'px-6 py-3 text-sm font-medium border-b-2 transition-colors duration-200'
                            ]"
                        >
                            Media Library
                        </button>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6" style="max-height: 60vh; overflow-y: auto;">
                        <!-- Upload Tab -->
                        <div v-if="selectedTab === 'upload'">
                            <!-- Drop Zone -->
                            <div
                                @drop.prevent="handleFileSelect"
                                @dragover.prevent
                                @dragenter.prevent
                                class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-gray-400 transition-colors duration-200"
                            >
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">
                                    Drop files here or click to select
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    PNG, JPG, GIF, MP4, PDF up to 10MB
                                </p>
                                <input
                                    ref="fileInput"
                                    type="file"
                                    multiple
                                    accept="image/*,video/*,application/pdf"
                                    @change="handleFileSelect"
                                    class="hidden"
                                />
                                <button
                                    @click="triggerFileInput"
                                    class="mt-4 px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 transition-colors duration-200"
                                >
                                    Select Files
                                </button>
                            </div>
                            
                            <!-- Preview Files -->
                            <div v-if="previewFiles.length > 0" class="mt-6">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Files to Upload</h4>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div
                                        v-for="(file, index) in previewFiles"
                                        :key="index"
                                        class="relative group"
                                    >
                                        <div class="aspect-w-1 aspect-h-1 bg-gray-100 rounded-lg overflow-hidden">
                                            <img
                                                v-if="file.type.startsWith('image/')"
                                                :src="file.preview"
                                                :alt="file.name"
                                                class="object-cover"
                                            />
                                            <div v-else class="flex items-center justify-center h-full">
                                                <span class="text-4xl">{{ getFileIcon(file.type) }}</span>
                                            </div>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-600 truncate">{{ file.name }}</p>
                                        <p class="text-xs text-gray-500">{{ file.size }}</p>
                                        <button
                                            @click="removePreview(index)"
                                            class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Upload Progress -->
                                <div v-if="uploading" class="mt-4">
                                    <div class="bg-gray-200 rounded-full h-2">
                                        <div
                                            :style="{ width: uploadProgress + '%' }"
                                            class="bg-sky-600 h-2 rounded-full transition-all duration-300"
                                        ></div>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Uploading... {{ uploadProgress }}%</p>
                                </div>
                                
                                <button
                                    @click="uploadFiles"
                                    :disabled="uploading"
                                    class="mt-4 px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    Upload Files
                                </button>
                            </div>
                        </div>
                        
                        <!-- Library Tab -->
                        <div v-if="selectedTab === 'library'">
                            <div v-if="existingMedia.length === 0" class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">No media files found</p>
                            </div>
                            
                            <div v-else class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                                <div
                                    v-for="media in existingMedia"
                                    :key="media.id"
                                    @click="toggleMediaSelection(media)"
                                    :class="[
                                        isSelected(media) ? 'ring-2 ring-sky-500' : '',
                                        'relative cursor-pointer rounded-lg overflow-hidden hover:shadow-lg transition-all duration-200'
                                    ]"
                                >
                                    <div class="aspect-w-1 aspect-h-1 bg-gray-100">
                                        <img
                                            v-if="media.mime_type.startsWith('image/')"
                                            :src="media.url"
                                            :alt="media.alt_text || media.name"
                                            class="object-cover"
                                        />
                                        <div v-else class="flex items-center justify-center h-full">
                                            <span class="text-4xl">{{ getFileIcon(media.mime_type) }}</span>
                                        </div>
                                    </div>
                                    <div
                                        v-if="isSelected(media)"
                                        class="absolute top-2 right-2 bg-sky-500 text-white rounded-full p-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="p-2">
                                        <p class="text-xs text-gray-600 truncate">{{ media.name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="text-sm text-gray-600">
                            <template v-if="selectedTab === 'upload'">
                                {{ previewFiles.length }} file(s) ready to upload
                            </template>
                            <template v-else>
                                {{ selectedMedia.length }} file(s) selected
                                <span v-if="selectedMedia.length > 0" class="ml-2 text-xs text-blue-600">
                                    ({{ selectedMedia.map(m => m.name).join(', ') }})
                                </span>
                            </template>
                        </div>
                        <div class="flex space-x-3">
                            <button
                                @click="closeModal"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500"
                            >
                                Cancel
                            </button>
                            <button
                                @click="insertMedia"
                                :disabled="(selectedTab === 'upload' ? previewFiles.length === 0 : selectedMedia.length === 0) || uploading"
                                class="px-4 py-2 text-sm font-medium text-white bg-sky-600 rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span v-if="uploading">
                                    Uploading...
                                </span>
                                <span v-else-if="selectedTab === 'upload' && previewFiles.length > 0">
                                    Upload & Insert
                                </span>
                                <span v-else>
                                    Insert Selected
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active {
    transition: opacity 0.3s;
}
.modal-enter-from, .modal-leave-to {
    opacity: 0;
}

.aspect-w-1 {
    position: relative;
    padding-bottom: 100%;
}

.aspect-h-1 {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}
</style>