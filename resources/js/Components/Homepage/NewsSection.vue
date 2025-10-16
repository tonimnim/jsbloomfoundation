<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    settings: Object,
    latestPosts: {
        type: Array,
        default: () => []
    }
});

// Format date
const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

// Get default image for posts without featured image
const getPostImage = (post) => {
    if (post.featured_image) {
        // Check if it's a relative path starting with /storage
        if (post.featured_image.startsWith('/storage')) {
            return post.featured_image;
        }
        // Check if it's already a full URL
        if (post.featured_image.startsWith('http')) {
            return post.featured_image;
        }
        // Otherwise prepend storage path
        return `/storage/${post.featured_image}`;
    }
    // Return a placeholder image
    return 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=400&h=300&fit=crop';
};

// Check if there are posts to display
const hasPosts = computed(() => props.latestPosts && props.latestPosts.length > 0);
</script>

<template>
    <section id="news" class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">Latest News</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Stay updated with our latest activities, events, and impact stories from the field
                </p>
            </div>

            <!-- News Grid -->
            <div v-if="hasPosts" class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <article 
                    v-for="post in latestPosts" 
                    :key="post.id"
                    class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300"
                >
                    <!-- Post Image -->
                    <Link :href="`/blog/${post.slug}`" class="block">
                        <div class="h-48 bg-gray-200 overflow-hidden">
                            <img 
                                :src="getPostImage(post)" 
                                :alt="post.title"
                                class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                @error="$event.target.src = 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=400&h=300&fit=crop'"
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
                            <time class="text-sm text-gray-500">{{ formatDate(post.published_at) }}</time>
                        </div>
                        
                        <!-- Title -->
                        <h3 class="text-xl font-bold text-gray-900 mb-3">
                            <Link :href="`/blog/${post.slug}`" class="hover:text-blue-600 transition-colors">
                                {{ post.title }}
                            </Link>
                        </h3>
                        
                        <!-- Excerpt -->
                        <p class="text-gray-600 mb-4 line-clamp-3">
                            {{ post.excerpt || 'Click to read more about this story...' }}
                        </p>
                        
                        <!-- Author & Read More -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">By {{ post.author_name }}</span>
                            <Link 
                                :href="`/blog/${post.slug}`"
                                class="text-blue-600 font-semibold hover:text-blue-800 transition-colors"
                            >
                                Read More →
                            </Link>
                        </div>
                    </div>
                </article>
            </div>

            <!-- No Posts Message -->
            <div v-else class="text-center py-12">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No news articles yet</h3>
                <p class="text-gray-500">Check back soon for updates on our latest activities and impact stories.</p>
            </div>

            <!-- View All Button -->
            <div v-if="hasPosts" class="text-center mt-12">
                <Link 
                    href="/blog"
                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-full transition-colors duration-300"
                >
                    View All News
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                </Link>
            </div>
        </div>
    </section>
</template>