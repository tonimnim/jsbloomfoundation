<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { debounce } from 'lodash';

const props = defineProps({
    posts: Object,
    categories: Array,
    filters: Object,
    sort: Object,
});

// Search and filter state
const search = ref(props.filters.search || '');
const statusFilter = ref(props.filters.status || '');
const categoryFilter = ref(props.filters.category || '');

// Sorting state
const sortField = ref(props.sort.field);
const sortDirection = ref(props.sort.direction);

// Selected posts for bulk actions
const selectedPosts = ref([]);
const selectAll = ref(false);

// Delete confirmation modal
const showDeleteModal = ref(false);
const postToDelete = ref(null);

// Watch for search changes with debounce
const debouncedSearch = debounce((value) => {
    router.get(route('admin.posts.index'), {
        search: value,
        status: statusFilter.value,
        category: categoryFilter.value,
        sort: sortField.value,
        direction: sortDirection.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300);

watch(search, debouncedSearch);

// Watch for filter changes
watch([statusFilter, categoryFilter], () => {
    router.get(route('admin.posts.index'), {
        search: search.value,
        status: statusFilter.value,
        category: categoryFilter.value,
        sort: sortField.value,
        direction: sortDirection.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
});

// Handle sorting
const sort = (field) => {
    if (sortField.value === field) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortField.value = field;
        sortDirection.value = 'asc';
    }
    
    router.get(route('admin.posts.index'), {
        search: search.value,
        status: statusFilter.value,
        category: categoryFilter.value,
        sort: sortField.value,
        direction: sortDirection.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Toggle select all
const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedPosts.value = props.posts.data.map(post => post.id);
    } else {
        selectedPosts.value = [];
    }
};

// Status badge color
const getStatusColor = (status) => {
    const colors = {
        draft: 'bg-gray-100 text-gray-800',
        published: 'bg-green-100 text-green-800',
        scheduled: 'bg-blue-100 text-blue-800',
        archived: 'bg-red-100 text-red-800',
    };
    return colors[status] || 'bg-gray-100 text-gray-800';
};

// Toggle featured status
const toggleFeatured = (post) => {
    router.put(route('admin.posts.toggle-featured', post.id), {}, {
        preserveScroll: true,
    });
};

// Delete post
const confirmDelete = (post) => {
    postToDelete.value = post;
    showDeleteModal.value = true;
};

const deletePost = () => {
    if (postToDelete.value) {
        router.delete(route('admin.posts.destroy', postToDelete.value.id), {
            onSuccess: () => {
                showDeleteModal.value = false;
                postToDelete.value = null;
            },
        });
    }
};

// Format date
const formatDate = (date) => {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
};
</script>

<template>
    <AdminLayout>
        <Head title="Blog Posts - Admin" />
        
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Blog Posts</h1>
                    <p class="mt-1 text-sm text-gray-600">Manage your blog content</p>
                </div>
                <Link 
                    :href="route('admin.posts.create')"
                    class="px-4 py-2 text-sm font-medium text-white bg-sky-600 rounded-lg hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500"
                >
                        <svg class="w-5 h-5 mr-2 -ml-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    New Post
                </Link>
            </div>
        </template>
        
        <!-- Filters -->
        <div class="mb-6 bg-white rounded-lg shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input
                        id="search"
                        v-model="search"
                        type="text"
                        placeholder="Search posts..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"
                    />
                </div>
                
                <!-- Status Filter -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select
                        id="status"
                        v-model="statusFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"
                    >
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                        <option value="scheduled">Scheduled</option>
                    </select>
                </div>
                
                <!-- Category Filter -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select
                        id="category"
                        v-model="categoryFilter"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-sky-500 focus:border-sky-500"
                    >
                        <option value="">All Categories</option>
                        <option v-for="category in categories" :key="category.id" :value="category.id">
                            {{ category.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Posts Table -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input
                                    v-model="selectAll"
                                    type="checkbox"
                                    @change="toggleSelectAll"
                                    class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-gray-300 rounded"
                                />
                            </th>
                            <th 
                                @click="sort('title')"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                            >
                                <div class="flex items-center">
                                    Title
                                    <svg v-if="sortField === 'title'" class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Author
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th 
                                @click="sort('published_at')"
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                            >
                                <div class="flex items-center">
                                    Published
                                    <svg v-if="sortField === 'published_at'" class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path v-if="sortDirection === 'asc'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="post in posts.data" :key="post.id" class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <input
                                    v-model="selectedPosts"
                                    :value="post.id"
                                    type="checkbox"
                                    class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-gray-300 rounded"
                                />
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ post.title }}</div>
                                        <div class="text-sm text-gray-500">{{ post.slug }}</div>
                                    </div>
                                    <button
                                        v-if="post.is_featured"
                                        @click="toggleFeatured(post)"
                                        class="ml-2 text-yellow-400 hover:text-yellow-500"
                                        title="Featured Post"
                                    >
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </button>
                                    <button
                                        v-else
                                        @click="toggleFeatured(post)"
                                        class="ml-2 text-gray-300 hover:text-yellow-500"
                                        title="Mark as Featured"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span 
                                    :style="{ backgroundColor: post.category_color + '20', color: post.category_color }"
                                    class="px-2 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{ post.category_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ post.author_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="getStatusColor(post.status)" class="px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ post.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ formatDate(post.published_at) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-3">
                                    <a
                                        :href="route('blog.show', post.slug)"
                                        target="_blank"
                                        class="text-gray-600 hover:text-gray-900"
                                        title="View Post"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <Link 
                                        :href="route('admin.posts.edit', post.id)"
                                        class="text-sky-600 hover:text-sky-900"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="confirmDelete(post)"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div v-if="posts.links.length > 3" class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Showing {{ posts.from }} to {{ posts.to }} of {{ posts.total }} results
                    </div>
                    <div class="flex space-x-1">
                        <Link
                            v-for="link in posts.links"
                            :key="link.label"
                            :href="link.url"
                            :class="[
                                link.active ? 'bg-sky-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                                'px-3 py-2 text-sm font-medium rounded-md'
                            ]"
                            v-html="link.label"
                            preserve-scroll
                        />
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showDeleteModal = false"></div>
                
                <div class="relative inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900">Delete Post</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">
                                        Are you sure you want to delete "{{ postToDelete?.title }}"? This action cannot be undone.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="px-4 py-3 bg-gray-50 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button
                            @click="deletePost"
                            type="button"
                            class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Delete
                        </button>
                        <button
                            @click="showDeleteModal = false"
                            type="button"
                            class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>