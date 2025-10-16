<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Get props from backend
const props = defineProps({
    stats: {
        type: Array,
        default: () => []
    },
    recentActivities: {
        type: Array,
        default: () => []
    },
    impactMetrics: {
        type: Array,
        default: () => []
    }
});

// Use data from props
const stats = props.stats;
const impactMetrics = props.impactMetrics;
const recentActivities = props.recentActivities;
</script>

<template>
    <Head title="Admin Dashboard" />

    <AdminLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                    <p class="text-gray-600">Welcome back, {{ user?.name }}! Here's what's happening at JS Bloom Foundation.</p>
                </div>
                <div class="text-sm text-gray-500">
                    Last updated: {{ new Date().toLocaleString() }}
                </div>
            </div>
        </template>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <component
                :is="stat.link ? 'a' : 'div'"
                v-for="stat in stats"
                :key="stat.name"
                :href="stat.link"
                class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200 hover:shadow-md transition-shadow"
                :class="stat.link ? 'cursor-pointer hover:border-sky-500' : ''"
            >
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg
                                class="h-12 w-12 text-gray-900"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" :d="stat.icon" />
                            </svg>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">{{ stat.name }}</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">{{ stat.value }}</div>
                                    <div
                                        v-if="stat.change !== '0%'"
                                        class="ml-2 flex items-baseline text-sm font-semibold"
                                        :class="stat.changeType === 'increase' ? 'text-green-600' : 'text-red-600'"
                                    >
                                        <svg
                                            v-if="stat.changeType === 'increase'"
                                            class="h-4 w-4 flex-shrink-0 self-center"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        <svg
                                            v-else
                                            class="h-4 w-4 flex-shrink-0 self-center"
                                            fill="currentColor"
                                            viewBox="0 0 20 20"
                                        >
                                            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                        {{ stat.change }}
                                    </div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </component>
        </div>

        <!-- Impact Metrics & Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Impact Metrics -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Impact Metrics</h3>
                    <p class="text-gray-600 text-sm">Progress towards our 2025 goals</p>
                </div>
                <div class="p-6 space-y-6">
                    <div v-for="metric in impactMetrics" :key="metric.name" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-900">{{ metric.name }}</span>
                            <span class="text-sm text-gray-600">{{ metric.value }} / {{ metric.target }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div 
                                class="bg-gradient-to-r from-sky-500 to-blue-600 h-2 rounded-full transition-all duration-500"
                                :style="{ width: `${metric.progress}%` }"
                            ></div>
                        </div>
                        <div class="text-xs text-gray-500">{{ metric.progress }}% complete</div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                    <p class="text-gray-600 text-sm">Latest updates across the platform</p>
                </div>
                <div class="p-6">
                    <div v-if="recentActivities && recentActivities.length > 0" class="space-y-4">
                        <div v-for="activity in recentActivities" :key="activity.id" class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-sky-100 rounded-full flex items-center justify-center">
                                    <div class="w-2 h-2 bg-sky-600 rounded-full"></div>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">{{ activity.title }}</p>
                                <p class="text-sm text-gray-600">{{ activity.description }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ activity.time }}</p>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <p class="text-gray-500 text-sm">No recent activity to display</p>
                    </div>
                    
                    <div v-if="recentActivities && recentActivities.length > 0" class="mt-6 pt-4 border-t border-gray-200">
                        <a href="/admin/logs" class="text-sm text-sky-600 hover:text-sky-700 font-medium">
                            View all activity →
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
                <p class="text-gray-600 text-sm">Common tasks and shortcuts</p>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <a href="/admin/posts/create" class="flex flex-col items-center p-4 bg-sky-50 rounded-lg hover:bg-sky-100 transition-colors">
                        <svg class="h-8 w-8 text-sky-600 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900">New Post</span>
                    </a>

                    <a href="/admin/applications" class="flex flex-col items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                        <svg class="h-8 w-8 text-purple-600 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900">Applications</span>
                    </a>

                    <a href="/admin/reports" class="flex flex-col items-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition-colors">
                        <svg class="h-8 w-8 text-orange-600 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                        <span class="text-sm font-medium text-gray-900">Reports</span>
                    </a>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
