<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    settings: Object,
    posts: Object,
    categories: {
        type: Array,
        default: () => []
    }
});

// Search and filter state
const search = ref('');
const selectedCategory = ref('');

// Format date
const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

// Get post image
const getPostImage = (post) => {
    if (post.featured_image) {
        if (post.featured_image.startsWith('/storage')) {
            return post.featured_image;
        }
        if (post.featured_image.startsWith('http')) {
            return post.featured_image;
        }
        return `/storage/${post.featured_image}`;
    }
    return 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=600&h=400&fit=crop';
};

// Handle search
const handleSearch = () => {
    router.get('/blog', {
        search: search.value,
        category: selectedCategory.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Handle category filter
const handleCategoryFilter = () => {
    router.get('/blog', {
        search: search.value,
        category: selectedCategory.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Clear filters
const clearFilters = () => {
    search.value = '';
    selectedCategory.value = '';
    router.get('/blog', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

// Check if filters are active
const hasActiveFilters = computed(() => search.value || selectedCategory.value);
</script>

<template>
    <PublicLayout :settings="settings">
        <Head title="News & Updates - JS Bloom Foundation" />

        <!-- Hero Section -->
        <section class="bg-gradient-to-br from-blue-600 to-blue-800 text-white py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="text-4xl lg:text-5xl font-bold mb-4">News & Updates</h1>
                    <p class="text-xl text-blue-100 max-w-3xl mx-auto">
                        Stay informed about our latest activities, impact stories, and community initiatives
                    </p>
                </div>
            </div>
        </section>
        
        <!-- Main Content -->
        <section class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Search and Filters -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <div class="grid md:grid-cols-3 gap-4">
                        <!-- Search -->
                        <div class="md:col-span-2">
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                                Search articles
                            </label>
                            <div class="relative">
                                <input
                                    id="search"
                                    v-model="search"
                                    type="text"
                                    placeholder="Search by title, content..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    @keyup.enter="handleSearch"
                                />
                                <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Category Filter -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                                Category
                            </label>
                            <select
                                id="category"
                                v-model="selectedCategory"
                                @change="handleCategoryFilter"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            >
                                <option value="">All Categories</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Active Filters -->
                    <div v-if="hasActiveFilters" class="mt-4 flex items-center justify-between">
                        <p class="text-sm text-gray-600">
                            Showing filtered results
                        </p>
                        <button
                            @click="clearFilters"
                            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                        >
                            Clear filters
                        </button>
                    </div>
                </div>
                
                <!-- Posts Grid -->
                <div v-if="posts.data && posts.data.length > 0" class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <article 
                        v-for="post in posts.data" 
                        :key="post.id"
                        class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300"
                    >
                        <!-- Post Image -->
                        <Link :href="`/blog/${post.slug}`" class="block">
                            <div class="h-56 bg-gray-200 overflow-hidden">
                                <img 
                                    :src="getPostImage(post)" 
                                    :alt="post.title"
                                    class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                    @error="$event.target.src = 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=600&h=400&fit=crop'"
                                />
                            </div>
                        </Link>
                        
                        <div class="p-6">
                            <!-- Category & Date -->
                            <div class="flex items-center justify-between mb-3">
                                <span 
                                    :style="{ backgroundColor: post.category_color + '20', color: post.category_color }"
                                    class="px-3 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{ post.category_name }}
                                </span>
                                <time class="text-sm text-gray-500" :datetime="post.published_at">
                                    {{ formatDate(post.published_at) }}
                                </time>
                            </div>
                            
                            <!-- Title -->
                            <h2 class="text-xl font-bold text-gray-900 mb-3">
                                <Link :href="`/blog/${post.slug}`" class="hover:text-blue-600 transition-colors">
                                    {{ post.title }}
                                </Link>
                            </h2>
                            
                            <!-- Excerpt -->
                            <p class="text-gray-600 mb-4 line-clamp-3">
                                {{ post.excerpt || 'Click to read more about this story...' }}
                            </p>
                            
                            <!-- Footer -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ post.author_name }}
                                </div>
                                <Link 
                                    :href="`/blog/${post.slug}`"
                                    class="text-blue-600 font-semibold hover:text-blue-800 transition-colors flex items-center"
                                >
                                    Read More
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>
                
                <!-- No Posts Message -->
                <div v-else class="text-center py-16 bg-white rounded-lg shadow-sm">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No articles found</h3>
                    <p class="text-gray-500 mb-6">
                        {{ hasActiveFilters ? 'Try adjusting your filters or search terms.' : 'Check back soon for updates on our latest activities.' }}
                    </p>
                    <button
                        v-if="hasActiveFilters"
                        @click="clearFilters"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                    >
                        Clear filters
                    </button>
                </div>
                
                <!-- Pagination -->
                <div v-if="posts.links && posts.links.length > 3" class="mt-12 flex justify-center">
                    <nav class="flex items-center space-x-2">
                        <Link
                            v-for="link in posts.links"
                            :key="link.label"
                            :href="link.url"
                            :class="[
                                'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
                                link.active 
                                    ? 'bg-blue-600 text-white' 
                                    : link.url 
                                        ? 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300' 
                                        : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                            ]"
                            :disabled="!link.url"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style scoped>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>