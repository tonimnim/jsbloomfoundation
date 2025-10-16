<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';

const props = defineProps({
    message: Object
});

// Reply form
const showReplyForm = ref(false);
const replyForm = useForm({
    subject: `Re: ${props.message.subject}`,
    reply_message: ''
});

// Send reply
const sendReply = () => {
    replyForm.post(route('admin.contacts.reply', props.message.id), {
        onSuccess: () => {
            showReplyForm.value = false;
            replyForm.reset('reply_message');
        }
    });
};

// Toggle read status
const toggleRead = () => {
    router.put(route('admin.contacts.toggle-read', props.message.id));
};

// Delete message
const deleteMessage = () => {
    if (confirm('Are you sure you want to delete this message?')) {
        router.delete(route('admin.contacts.destroy', props.message.id));
    }
};

// Format date
const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
};

// Copy email to clipboard
const copyEmail = () => {
    navigator.clipboard.writeText(props.message.email);
    alert('Email copied to clipboard!');
};
</script>

<template>
    <AdminLayout>
        <Head :title="`Message from ${message.first_name} ${message.last_name} - Admin`" />
        
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Contact Message</h1>
                    <p class="mt-1 text-sm text-gray-600">View and respond to contact form submission</p>
                </div>
                <Link
                    :href="route('admin.contacts.index')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    Back to Messages
                </Link>
            </div>
        </template>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Message Details -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-start justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Message Details</h3>
                        <span 
                            :class="message.is_read ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                            class="px-2 py-1 text-xs font-semibold rounded-full"
                        >
                            {{ message.is_read ? 'Read' : 'Unread' }}
                        </span>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Subject</label>
                            <p class="mt-1 text-lg font-medium text-gray-900">{{ message.subject }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Message</label>
                            <div class="mt-1 p-4 bg-gray-50 rounded-lg">
                                <p class="text-gray-700 whitespace-pre-wrap">{{ message.message }}</p>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Received On</label>
                            <p class="mt-1 text-sm text-gray-900">{{ formatDate(message.created_at) }}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Reply Section -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Reply</h3>
                        <button
                            v-if="!showReplyForm"
                            @click="showReplyForm = true"
                            class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700"
                        >
                            Compose Reply
                        </button>
                    </div>
                    
                    <div v-if="showReplyForm">
                        <form @submit.prevent="sendReply" class="space-y-4">
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                                    Subject
                                </label>
                                <input
                                    id="subject"
                                    v-model="replyForm.subject"
                                    type="text"
                                    required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': replyForm.errors.subject }"
                                />
                                <p v-if="replyForm.errors.subject" class="mt-1 text-sm text-red-600">{{ replyForm.errors.subject }}</p>
                            </div>
                            
                            <div>
                                <label for="reply_message" class="block text-sm font-medium text-gray-700 mb-2">
                                    Message
                                </label>
                                <textarea
                                    id="reply_message"
                                    v-model="replyForm.reply_message"
                                    rows="6"
                                    required
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': replyForm.errors.reply_message }"
                                    placeholder="Type your reply here..."
                                ></textarea>
                                <p v-if="replyForm.errors.reply_message" class="mt-1 text-sm text-red-600">{{ replyForm.errors.reply_message }}</p>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button
                                    type="button"
                                    @click="showReplyForm = false"
                                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    :disabled="replyForm.processing"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span v-if="replyForm.processing">Sending...</span>
                                    <span v-else>Send Reply</span>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <p>Click "Compose Reply" to send a response to this message.</p>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Sender Information -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Sender Information</h3>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ message.first_name }} {{ message.last_name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <div class="mt-1 flex items-center">
                                <a 
                                    :href="`mailto:${message.email}`"
                                    class="text-sm text-blue-600 hover:text-blue-800"
                                >
                                    {{ message.email }}
                                </a>
                                <button
                                    @click="copyEmail"
                                    class="ml-2 text-gray-400 hover:text-gray-600"
                                    title="Copy email"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div v-if="message.phone">
                            <label class="block text-sm font-medium text-gray-500">Phone</label>
                            <a 
                                :href="`tel:${message.phone}`"
                                class="mt-1 text-sm text-blue-600 hover:text-blue-800"
                            >
                                {{ message.phone }}
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                    
                    <div class="space-y-3">
                        <button
                            @click="toggleRead"
                            :class="[
                                'w-full px-4 py-2 text-center rounded-lg transition-colors',
                                message.is_read
                                    ? 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'
                                    : 'bg-green-100 text-green-700 hover:bg-green-200'
                            ]"
                        >
                            Mark as {{ message.is_read ? 'Unread' : 'Read' }}
                        </button>
                        
                        <a
                            :href="`mailto:${message.email}?subject=Re: ${encodeURIComponent(message.subject)}`"
                            class="block w-full px-4 py-2 bg-blue-100 text-blue-700 text-center rounded-lg hover:bg-blue-200 transition-colors"
                        >
                            Reply via Email Client
                        </a>
                        
                        <button
                            @click="deleteMessage"
                            class="w-full px-4 py-2 bg-red-100 text-red-700 text-center rounded-lg hover:bg-red-200 transition-colors"
                        >
                            Delete Message
                        </button>
                    </div>
                </div>
                
                <!-- Message Info -->
                <div class="bg-gray-50 rounded-lg p-4 text-sm text-gray-600">
                    <p><strong>Message ID:</strong> #{{ message.id }}</p>
                    <p><strong>Received:</strong> {{ formatDate(message.created_at) }}</p>
                    <p v-if="message.updated_at !== message.created_at">
                        <strong>Last Updated:</strong> {{ formatDate(message.updated_at) }}
                    </p>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>