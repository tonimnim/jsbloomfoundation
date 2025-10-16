<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RichTextEditor from '@/Components/Admin/RichTextEditor.vue';

const props = defineProps({
    categories: Array,
    existingMedia: {
        type: Array,
        default: () => []
    }
});

// Form data
const form = useForm({
    title: '',
    slug: '',
    excerpt: '',
    content: '',
    category_id: props.categories && props.categories.length > 0 ? props.categories[0].id : null,
    status: 'draft',
    is_featured: false,
    allow_comments: true,
    // published_at will be set by server when publishing
    featured_image: '',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
});

// Featured image file input
const featuredImageInput = ref(null);

// Auto-generate slug from title
const autoGenerateSlug = computed(() => {
    return form.title
        .toLowerCase()
        .replace(/[^\w\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .trim();
});

// Watch title changes to update slug
watch(() => form.title, (newTitle) => {
    if (!form.slug || form.slug === autoGenerateSlug.value) {
        form.slug = newTitle
            .toLowerCase()
            .replace(/[^\w\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }
});

// Handle featured image upload
const selectFeaturedImage = () => {
    featuredImageInput.value.click();
};

const handleFeaturedImageUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    // Validate file type
    if (!file.type.startsWith('image/')) {
        alert('Please select an image file');
        return;
    }
    
    // Validate file size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        alert('Image size must be less than 5MB');
        return;
    }
    
    try {
        // Upload to server
        const formData = new FormData();
        formData.append('files', file); // Send as single file, not array notation
        
        const response = await axios.post('/admin/media/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        });
        
        if (response.data && response.data.files && response.data.files[0]) {
            form.featured_image = response.data.files[0].url;
        }
    } catch (error) {
        console.error('Featured image upload failed:', error);
        if (error.response && error.response.status === 419) {
            alert('Your session has expired. The page will refresh.');
            window.location.reload();
        } else {
            alert('Failed to upload image. Please try again.');
        }
    }
    
    // Reset input
    event.target.value = '';
};

// Submit form
const submit = () => {
    form.post(route('admin.posts.store'), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by controller
        },
        onError: (errors) => {
            if (errors.message && errors.message.includes('419')) {
                alert('Your session has expired. The page will refresh.');
                window.location.reload();
            } else {
                // Show validation message
                const firstError = Object.values(errors)[0];
                alert('Validation Error: ' + firstError);
            }
        },
    });
};

// Save as draft
const saveAsDraft = () => {
    form.status = 'draft';
    submit();
};

// Publish
const publish = () => {
    form.status = 'published';
    // Don't set published_at here - let the server handle it
    submit();
};

// Remove featured image
const removeFeaturedImage = () => {
    form.featured_image = '';
};

// Character count for excerpt
const excerptCharCount = computed(() => {
    return form.excerpt ? form.excerpt.length : 0;
});

// SEO Preview
const seoTitle = computed(() => {
    return form.meta_title || form.title || 'Untitled Post';
});

const seoDescription = computed(() => {
    return form.meta_description || form.excerpt || 'No description available';
});

// Handle auto-save from editor
const handleAutoSave = (autoSaveData) => {
    // Store the auto-saved content with timestamp
    const autoSaveKey = `blog-post-autosave-${form.slug || 'new'}`;
    const autoSavePayload = {
        content: autoSaveData.content,
        timestamp: autoSaveData.timestamp,
        formData: {
            title: form.title,
            slug: form.slug,
            excerpt: form.excerpt,
            category_id: form.category_id,
            meta_title: form.meta_title,
            meta_description: form.meta_description,
            meta_keywords: form.meta_keywords,
        }
    };
    
    try {
        localStorage.setItem(autoSaveKey, JSON.stringify(autoSavePayload));
    } catch (error) {
        console.error('Auto-save failed:', error);
    }
};
</script>

<template>
    <AdminLayout>
        <Head title="Create New Post - Admin" />
        
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Create New Post</h1>
                    <p class="mt-1 text-sm text-gray-600">Write and publish a new blog post</p>
                </div>
            </div>
        </template>

        <form @submit.prevent="submit" class="space-y-6">
            <!-- Validation Summary -->
            <div v-if="Object.keys(form.errors).length > 0" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <h3 class="font-semibold mb-2">Please fix the following errors:</h3>
                <ul class="list-disc list-inside text-sm">
                    <li v-for="(error, field) in form.errors" :key="field">{{ error }}</li>
                </ul>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Post Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            placeholder="Enter post title"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-transparent text-lg"
                            :class="{ 'border-red-500': form.errors.title }"
                        />
                        <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                        
                        <!-- Slug -->
                        <div class="mt-4">
                            <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                                URL Slug
                            </label>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-500 mr-2">{{ $page.props.app.url }}/blog/</span>
                                <input
                                    id="slug"
                                    v-model="form.slug"
                                    type="text"
                                    placeholder="post-url-slug"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-transparent text-sm"
                                    :class="{ 'border-red-500': form.errors.slug }"
                                />
                            </div>
                            <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                        </div>
                    </div>

                    <!-- Content Editor -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <RichTextEditor
                            v-model="form.content"
                            :auto-save-key="`blog-post-draft-${form.slug || 'new'}`"
                            placeholder="Start writing your blog post content..."
                            @auto-save="handleAutoSave"
                        />
                        <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                    </div>

                    <!-- Excerpt -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">
                            Excerpt
                        </label>
                        <textarea
                            id="excerpt"
                            v-model="form.excerpt"
                            rows="3"
                            maxlength="500"
                            placeholder="Brief description of your post (optional)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-transparent resize-none"
                            :class="{ 'border-red-500': form.errors.excerpt }"
                        ></textarea>
                        <div class="mt-1 flex justify-between">
                            <p v-if="form.errors.excerpt" class="text-sm text-red-600">{{ form.errors.excerpt }}</p>
                            <span class="text-sm text-gray-500">{{ excerptCharCount }}/500</span>
                        </div>
                    </div>

                    <!-- SEO Settings -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Settings</h3>
                        
                        <!-- SEO Preview -->
                        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Preview</p>
                            <h4 class="text-blue-600 text-lg font-medium hover:underline cursor-pointer">
                                {{ seoTitle }}
                            </h4>
                            <p class="text-sm text-green-700">{{ $page.props.app.url }}/blog/{{ form.slug || 'post-url' }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ seoDescription }}</p>
                        </div>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">
                                    Meta Title
                                </label>
                                <input
                                    id="meta_title"
                                    v-model="form.meta_title"
                                    type="text"
                                    placeholder="SEO optimized title (optional)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                />
                            </div>
                            
                            <div>
                                <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">
                                    Meta Description
                                </label>
                                <textarea
                                    id="meta_description"
                                    v-model="form.meta_description"
                                    rows="2"
                                    placeholder="SEO description (optional)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-transparent resize-none"
                                ></textarea>
                            </div>
                            
                            <div>
                                <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">
                                    Meta Keywords
                                </label>
                                <input
                                    id="meta_keywords"
                                    v-model="form.meta_keywords"
                                    type="text"
                                    placeholder="keyword1, keyword2, keyword3 (optional)"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Publish Options -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Publish</h3>
                        
                        <div class="space-y-4">
                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <div class="flex items-center space-x-2">
                                    <span :class="[
                                        form.status === 'draft' ? 'bg-gray-100 text-gray-800' : 
                                        form.status === 'published' ? 'bg-green-100 text-green-800' :
                                        'bg-blue-100 text-blue-800',
                                        'px-2 py-1 text-xs font-semibold rounded-full'
                                    ]">
                                        {{ form.status }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Actions -->
                            <div class="pt-4 space-y-3">
                                <button
                                    @click="publish"
                                    type="button"
                                    :disabled="form.processing || !form.title || !form.content || !form.category_id"
                                    class="w-full px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="form.processing">Publishing...</span>
                                    <span v-else>Publish Now</span>
                                </button>
                                <button
                                    @click="saveAsDraft"
                                    type="button"
                                    :disabled="form.processing || !form.title || !form.content || !form.category_id"
                                    class="w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="form.processing">Saving...</span>
                                    <span v-else>Save as Draft</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="category"
                            v-model="form.category_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                            :class="{ 'border-red-500': form.errors.category_id }"
                        >
                            <option value="">Select a category</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                        <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
                    </div>

                    <!-- Featured Image -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Featured Image
                        </label>
                        
                        <div v-if="!form.featured_image" class="space-y-3">
                            <button
                                @click="selectFeaturedImage"
                                type="button"
                                class="w-full px-4 py-8 border-2 border-dashed border-gray-300 rounded-lg hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500"
                            >
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-600">Click to select featured image from device</p>
                            </button>
                        </div>
                        
                        <div v-else class="space-y-3">
                            <img
                                :src="form.featured_image"
                                alt="Featured image"
                                class="w-full h-40 object-cover rounded-lg"
                            />
                            <button
                                @click="removeFeaturedImage"
                                type="button"
                                class="w-full px-3 py-2 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                            >
                                Remove Image
                            </button>
                        </div>
                    </div>

                    <!-- Additional Options -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Options</h3>
                        
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input
                                    v-model="form.is_featured"
                                    type="checkbox"
                                    class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-gray-300 rounded"
                                />
                                <span class="ml-2 text-sm text-gray-700">Mark as Featured Post</span>
                            </label>
                            
                            <label class="flex items-center">
                                <input
                                    v-model="form.allow_comments"
                                    type="checkbox"
                                    class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-gray-300 rounded"
                                />
                                <span class="ml-2 text-sm text-gray-700">Allow Comments</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Hidden file input for featured image -->
        <input
            ref="featuredImageInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleFeaturedImageUpload"
        />
    </AdminLayout>
</template>