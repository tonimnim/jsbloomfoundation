<script setup>
import { Head, useForm, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RichTextEditor from '@/Components/Admin/RichTextEditor.vue';

const props = defineProps({
    post: Object,
    categories: Array
});

const form = useForm({
    title: props.post.title,
    slug: props.post.slug,
    excerpt: props.post.excerpt || '',
    content: props.post.content,
    category_id: props.post.category_id,
    status: props.post.status,
    is_featured: Boolean(props.post.is_featured),
    allow_comments: Boolean(props.post.allow_comments),
    // published_at handled by server
    featured_image: props.post.featured_image || '',
    meta_title: props.post.meta_title || '',
    meta_description: props.post.meta_description || '',
    meta_keywords: props.post.meta_keywords || '',
});

// Featured image file input
const featuredImageInput = ref(null);

// Generate slug from title
const generateSlug = () => {
    if (form.title) {
        form.slug = form.title
            .toLowerCase()
            .replace(/[^\w\s-]/g, '') // Remove non-word chars
            .replace(/\s+/g, '-') // Replace spaces with -
            .replace(/--+/g, '-') // Replace multiple - with single -
            .trim();
    }
};

// Auto-generate excerpt from content
const generateExcerpt = () => {
    if (form.content) {
        // Strip HTML tags and get first 150 characters
        const plainText = form.content.replace(/<[^>]*>/g, '');
        form.excerpt = plainText.substring(0, 150).trim() + '...';
    }
};

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
    form.put(route('admin.posts.update', props.post.id), {
        preserveScroll: true,
        onSuccess: () => {
            // Redirect handled by controller
        },
        onError: (errors) => {
            if (errors.message && errors.message.includes('419')) {
                alert('Your session has expired. The page will refresh.');
                window.location.reload();
            }
        },
    });
};

// Preview post
const previewPost = () => {
    // Open in new tab
    window.open(route('blog.show', props.post.slug), '_blank');
};

// Character counts
const titleCharCount = computed(() => form.title.length);
const metaTitleCharCount = computed(() => form.meta_title.length);
const metaDescriptionCharCount = computed(() => form.meta_description.length);

// Get app URL from page props
const appUrl = computed(() => usePage().props.app.url || '');
</script>

<template>
    <AdminLayout>
        <Head title="Edit Post - Admin" />
        
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Post</h1>
                    <p class="mt-1 text-sm text-gray-600">Update your blog post</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button
                        @click="previewPost"
                        type="button"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500"
                    >
                        <svg class="w-5 h-5 mr-2 -ml-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Preview
                    </button>
                    <a
                        :href="route('admin.posts.index')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500"
                    >
                        Cancel
                    </a>
                </div>
            </div>
        </template>
        
        <form @submit.prevent="submit">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                            Title <span class="text-red-500">*</span>
                        </label>
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"
                            :class="{ 'border-red-500': form.errors.title }"
                            required
                        />
                        <div class="mt-1 flex items-center justify-between">
                            <p v-if="form.errors.title" class="text-sm text-red-600">{{ form.errors.title }}</p>
                            <p class="text-sm text-gray-500">{{ titleCharCount }} characters</p>
                        </div>
                    </div>
                    
                    <!-- Slug -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label for="slug" class="block text-sm font-medium text-gray-700 mb-2">
                            Slug (URL)
                        </label>
                        <div class="flex items-center space-x-2">
                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"
                                :class="{ 'border-red-500': form.errors.slug }"
                                placeholder="leave-empty-to-auto-generate"
                            />
                            <button
                                @click="generateSlug"
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                            >
                                Generate
                            </button>
                        </div>
                        <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                        <p class="mt-1 text-sm text-gray-500">
                            URL: {{ appUrl }}/blog/{{ form.slug || 'your-post-slug' }}
                        </p>
                    </div>
                    
                    <!-- Content -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label for="content" class="block text-sm font-medium text-gray-700 mb-2">
                            Content <span class="text-red-500">*</span>
                        </label>
                        <RichTextEditor
                            v-model="form.content"
                        />
                        <p v-if="form.errors.content" class="mt-1 text-sm text-red-600">{{ form.errors.content }}</p>
                    </div>
                    
                    <!-- Excerpt -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <label for="excerpt" class="block text-sm font-medium text-gray-700 mb-2">
                            Excerpt
                        </label>
                        <div class="space-y-2">
                            <textarea
                                id="excerpt"
                                v-model="form.excerpt"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"
                                :class="{ 'border-red-500': form.errors.excerpt }"
                                placeholder="Brief description of your post..."
                            ></textarea>
                            <button
                                @click="generateExcerpt"
                                type="button"
                                class="text-sm text-sky-600 hover:text-sky-700"
                            >
                                Generate from content
                            </button>
                        </div>
                        <p v-if="form.errors.excerpt" class="mt-1 text-sm text-red-600">{{ form.errors.excerpt }}</p>
                    </div>
                    
                    <!-- SEO Settings -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">SEO Settings</h3>
                        
                        <!-- Meta Title -->
                        <div class="mb-4">
                            <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-2">
                                Meta Title
                            </label>
                            <input
                                id="meta_title"
                                v-model="form.meta_title"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"
                                :class="{ 'border-red-500': form.errors.meta_title }"
                                placeholder="Leave empty to use post title"
                            />
                            <div class="mt-1 flex items-center justify-between">
                                <p v-if="form.errors.meta_title" class="text-sm text-red-600">{{ form.errors.meta_title }}</p>
                                <p class="text-sm" :class="metaTitleCharCount > 60 ? 'text-red-600' : 'text-gray-500'">
                                    {{ metaTitleCharCount }}/60 characters
                                </p>
                            </div>
                        </div>
                        
                        <!-- Meta Description -->
                        <div class="mb-4">
                            <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-2">
                                Meta Description
                            </label>
                            <textarea
                                id="meta_description"
                                v-model="form.meta_description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"
                                :class="{ 'border-red-500': form.errors.meta_description }"
                                placeholder="Brief description for search engines..."
                            ></textarea>
                            <div class="mt-1 flex items-center justify-between">
                                <p v-if="form.errors.meta_description" class="text-sm text-red-600">{{ form.errors.meta_description }}</p>
                                <p class="text-sm" :class="metaDescriptionCharCount > 160 ? 'text-red-600' : 'text-gray-500'">
                                    {{ metaDescriptionCharCount }}/160 characters
                                </p>
                            </div>
                        </div>
                        
                        <!-- Meta Keywords -->
                        <div>
                            <label for="meta_keywords" class="block text-sm font-medium text-gray-700 mb-2">
                                Meta Keywords
                            </label>
                            <input
                                id="meta_keywords"
                                v-model="form.meta_keywords"
                                type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"
                                :class="{ 'border-red-500': form.errors.meta_keywords }"
                                placeholder="keyword1, keyword2, keyword3"
                            />
                            <p v-if="form.errors.meta_keywords" class="mt-1 text-sm text-red-600">{{ form.errors.meta_keywords }}</p>
                            <p class="mt-1 text-sm text-gray-500">Separate keywords with commas</p>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Publish Settings -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Publish Settings</h3>
                        
                        <!-- Status -->
                        <div class="mb-4">
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"
                                :class="{ 'border-red-500': form.errors.status }"
                                required
                            >
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
                        </div>
                        
                        <!-- Category -->
                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Category <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="category_id"
                                v-model="form.category_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"
                                :class="{ 'border-red-500': form.errors.category_id }"
                                required
                            >
                                <option value="">Select a category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.category_id" class="mt-1 text-sm text-red-600">{{ form.errors.category_id }}</p>
                        </div>
                        
                        <!-- Options -->
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input
                                    v-model="form.is_featured"
                                    type="checkbox"
                                    class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-gray-300 rounded"
                                />
                                <span class="ml-2 text-sm text-gray-700">Featured Post</span>
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
                    
                    <!-- Featured Image -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Featured Image</h3>
                        
                        <div v-if="form.featured_image" class="mb-4">
                            <img
                                :src="form.featured_image"
                                alt="Featured image"
                                class="w-full h-48 object-cover rounded-lg"
                            />
                            <button
                                @click="form.featured_image = ''"
                                type="button"
                                class="mt-2 text-sm text-red-600 hover:text-red-700"
                            >
                                Remove Image
                            </button>
                        </div>
                        
                        <button
                            @click="selectFeaturedImage"
                            type="button"
                            class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                        >
                            <svg class="w-5 h-5 mr-2 -ml-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Select Image from Device
                        </button>
                        
                        <p v-if="form.errors.featured_image" class="mt-1 text-sm text-red-600">{{ form.errors.featured_image }}</p>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-sky-600 rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="form.processing">Updating...</span>
                            <span v-else>Update Post</span>
                        </button>
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