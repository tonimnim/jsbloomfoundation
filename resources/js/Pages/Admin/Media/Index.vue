<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import MediaUploadModal from '@/Components/Admin/MediaUploadModal.vue';

const props = defineProps({
    media: Object,
    filters: Object,
});

// State
const showUploadModal = ref(false);
const selectedMedia = ref([]);
const searchQuery = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || '');

// Computed
const hasSelection = computed(() => selectedMedia.value.length > 0);

// Methods
const search = () => {
    router.get(route('admin.media.index'), {
        search: searchQuery.value,
        type: typeFilter.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    searchQuery.value = '';
    typeFilter.value = '';
    router.get(route('admin.media.index'));
};

const toggleSelection = (media) => {
    const index = selectedMedia.value.findIndex(m => m.id === media.id);
    if (index > -1) {
        selectedMedia.value.splice(index, 1);
    } else {
        selectedMedia.value.push(media);
    }
};

const isSelected = (media) => {
    return selectedMedia.value.some(m => m.id === media.id);
};

const deleteSelected = () => {
    if (!confirm(`Are you sure you want to delete ${selectedMedia.value.length} file(s)?`)) {
        return;
    }
    
    selectedMedia.value.forEach(media => {
        router.delete(route('admin.media.destroy', media.id), {
            preserveScroll: true,
        });
    });
    
    selectedMedia.value = [];
};

const handleUploadComplete = () => {
    showUploadModal.value = false;
    router.reload();
};

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

const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>

<template>
    <AdminLayout>
        <Head title="Media Library - Admin" />
        
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Media Library</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage your uploaded files and media</p>
                </div>
                <button
                    @click="showUploadModal = true"
                    class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500"
                >
                    Upload Files
                </button>
            </div>
        </template>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                        Search
                    </label>
                    <input
                        id="search"
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search files..."
                        @keyup.enter="search"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                    />
                </div>
                
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        File Type
                    </label>
                    <select
                        id="type"
                        v-model="typeFilter"
                        @change="search"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                    >
                        <option value="">All Types</option>
                        <option value="image">Images</option>
                        <option value="video">Videos</option>
                        <option value="document">Documents</option>
                    </select>
                </div>
                
                <div class="flex items-end gap-2">
                    <button
                        @click="search"
                        class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500"
                    >
                        Search
                    </button>
                    <button
                        @click="clearFilters"
                        class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                    >
                        Clear
                    </button>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div v-if="hasSelection" class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">
                    {{ selectedMedia.length }} file(s) selected
                </span>
                <button
                    @click="deleteSelected"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                >
                    Delete Selected
                </button>
            </div>
        </div>

        <!-- Media Grid -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div v-if="media.data.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-2 text-sm text-gray-600">No media files found</p>
                <button
                    @click="showUploadModal = true"
                    class="mt-4 px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500"
                >
                    Upload Your First File
                </button>
            </div>
            
            <div v-else class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                <div
                    v-for="file in media.data"
                    :key="file.id"
                    @click="toggleSelection(file)"
                    :class="[
                        isSelected(file) ? 'ring-2 ring-sky-500' : '',
                        'relative cursor-pointer rounded-lg overflow-hidden hover:shadow-lg transition-all duration-200 bg-gray-50'
                    ]"
                >
                    <div class="aspect-w-1 aspect-h-1 bg-gray-100">
                        <img
                            v-if="file.mime_type.startsWith('image/')"
                            :src="file.thumbnail_url || file.url"
                            :alt="file.alt_text || file.name"
                            class="object-cover w-full h-full"
                        />
                        <div v-else class="flex items-center justify-center h-full">
                            <span class="text-4xl">{{ getFileIcon(file.mime_type) }}</span>
                        </div>
                    </div>
                    
                    <div
                        v-if="isSelected(file)"
                        class="absolute top-2 right-2 bg-sky-500 text-white rounded-full p-1"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    
                    <div class="p-3">
                        <p class="text-xs font-medium text-gray-900 truncate">{{ file.original_name }}</p>
                        <p class="text-xs text-gray-500">{{ formatFileSize(file.file_size) }}</p>
                        <p class="text-xs text-gray-400">{{ new Date(file.created_at).toLocaleDateString() }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Pagination -->
            <div v-if="media.links && media.links.length > 3" class="mt-6 flex justify-center">
                <nav class="flex items-center space-x-2">
                    <template v-for="link in media.links" :key="link.label">
                        <button
                            v-if="link.url"
                            @click="router.get(link.url)"
                            :class="[
                                link.active ? 'bg-sky-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                                'px-3 py-2 text-sm border border-gray-300 rounded-lg'
                            ]"
                            v-html="link.label"
                        ></button>
                        <span
                            v-else
                            class="px-3 py-2 text-sm text-gray-400"
                            v-html="link.label"
                        ></span>
                    </template>
                </nav>
            </div>
        </div>

        <!-- Upload Modal -->
        <MediaUploadModal
            :show="showUploadModal"
            :existing-media="[]"
            @close="showUploadModal = false"
            @select="handleUploadComplete"
            @uploaded="handleUploadComplete"
        />
    </AdminLayout>
</template>

<style scoped>
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