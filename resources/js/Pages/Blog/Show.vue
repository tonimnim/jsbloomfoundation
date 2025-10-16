<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';

const props = defineProps({
    settings: Object,
    post: Object,
    relatedPosts: {
        type: Array,
        default: () => []
    }
});

const $page = usePage();

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
    return 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=1200&h=600&fit=crop';
};

// Parse meta data
const metaData = computed(() => {
    if (props.post.meta_data) {
        try {
            return JSON.parse(props.post.meta_data);
        } catch (e) {
            return {};
        }
    }
    return {};
});

// Format reading time
const readingTime = computed(() => {
    const wordsPerMinute = 200;
    const words = props.post.content.replace(/<[^>]*>/g, '').split(/\s+/).length;
    const minutes = Math.ceil(words / wordsPerMinute);
    return `${minutes} min read`;
});
</script>

<template>
    <PublicLayout :settings="settings">
        <Head>
            <title>{{ metaData.title || post.title }} - Ustawi Wa Jamii</title>
            <meta name="description" :content="metaData.description || post.excerpt" />
            <meta name="keywords" :content="metaData.keywords" />
            <meta property="og:title" :content="post.title" />
            <meta property="og:description" :content="post.excerpt" />
            <meta property="og:image" :content="getPostImage(post)" />
            <meta property="og:type" content="article" />
            <meta property="article:published_time" :content="post.published_at" />
            <meta property="article:author" :content="post.author_name" />
        </Head>
        
        <!-- Article Header -->
        <article>
            <header class="bg-gray-50 py-12">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Breadcrumb -->
                    <nav class="flex items-center text-sm text-gray-500 mb-6">
                        <Link href="/" class="hover:text-gray-700">Home</Link>
                        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <Link href="/blog" class="hover:text-gray-700">News</Link>
                        <svg class="w-4 h-4 mx-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="text-gray-900">{{ post.title }}</span>
                    </nav>
                    
                    <!-- Category -->
                    <div class="mb-4">
                        <span 
                            :style="{ backgroundColor: post.category_color + '20', color: post.category_color }"
                            class="inline-block px-4 py-2 text-sm font-semibold rounded-full"
                        >
                            {{ post.category_name }}
                        </span>
                    </div>
                    
                    <!-- Title -->
                    <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-6">
                        {{ post.title }}
                    </h1>
                    
                    <!-- Meta Info -->
                    <div class="flex flex-wrap items-center text-gray-600 space-x-6">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span>By {{ post.author_name }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <time :datetime="post.published_at">{{ formatDate(post.published_at) }}</time>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>{{ readingTime }}</span>
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span>{{ post.views_count }} views</span>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Featured Image -->
            <div v-if="post.featured_image" class="bg-gray-100">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <img 
                        :src="getPostImage(post)" 
                        :alt="post.title"
                        class="w-full h-auto rounded-lg shadow-lg"
                        @error="$event.target.src = 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=1200&h=600&fit=crop'"
                    />
                </div>
            </div>
            
            <!-- Article Content -->
            <div class="py-12">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    <!-- Content -->
                    <div 
                        class="prose prose-lg max-w-none"
                        v-html="post.content"
                    ></div>
                    
                    <!-- Share Section -->
                    <div class="mt-12 pt-8 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Share this article</h3>
                        <div class="flex space-x-4">
                            <a
                                :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent($page.url)}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                            >
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                                Facebook
                            </a>
                            <a
                                :href="`https://twitter.com/intent/tweet?text=${encodeURIComponent(post.title)}&url=${encodeURIComponent($page.url)}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition-colors"
                            >
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                                Twitter
                            </a>
                            <a
                                :href="`https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent($page.url)}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex items-center px-4 py-2 bg-blue-700 text-white rounded-lg hover:bg-blue-800 transition-colors"
                            >
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                </svg>
                                LinkedIn
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </article>
        
        <!-- Related Posts -->
        <section v-if="relatedPosts.length > 0" class="py-12 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-8">Related Articles</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <article 
                        v-for="relatedPost in relatedPosts" 
                        :key="relatedPost.id"
                        class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300"
                    >
                        <Link :href="`/blog/${relatedPost.slug}`" class="block">
                            <div class="h-48 bg-gray-200 overflow-hidden">
                                <img 
                                    :src="getPostImage(relatedPost)" 
                                    :alt="relatedPost.title"
                                    class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                    @error="$event.target.src = 'https://images.unsplash.com/photo-1488521787991-ed7bbaae773c?w=600&h=400&fit=crop'"
                                />
                            </div>
                        </Link>
                        
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-3">
                                <span 
                                    :style="{ backgroundColor: relatedPost.category_color + '20', color: relatedPost.category_color }"
                                    class="px-3 py-1 text-xs font-semibold rounded-full"
                                >
                                    {{ relatedPost.category_name }}
                                </span>
                                <time class="text-sm text-gray-500">
                                    {{ formatDate(relatedPost.published_at) }}
                                </time>
                            </div>
                            
                            <h3 class="text-xl font-bold text-gray-900 mb-3">
                                <Link :href="`/blog/${relatedPost.slug}`" class="hover:text-blue-600 transition-colors">
                                    {{ relatedPost.title }}
                                </Link>
                            </h3>
                            
                            <p class="text-gray-600 line-clamp-2">
                                {{ relatedPost.excerpt }}
                            </p>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style scoped>
/* Prose styling for article content */
.prose {
    @apply text-gray-700;
}

.prose h1,
.prose h2,
.prose h3,
.prose h4,
.prose h5,
.prose h6 {
    @apply text-gray-900 font-bold;
}

.prose a {
    @apply text-blue-600 underline hover:text-blue-800;
}

.prose ul {
    @apply list-disc list-inside;
}

.prose ol {
    @apply list-decimal list-inside;
}

.prose img {
    @apply rounded-lg shadow-md my-6;
}

.prose blockquote {
    @apply border-l-4 border-blue-500 pl-4 italic text-gray-600;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>