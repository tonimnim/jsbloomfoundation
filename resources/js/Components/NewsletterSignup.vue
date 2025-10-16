<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'default' // 'default', 'minimal', 'inline'
    }
});

const form = useForm({
    email: '',
    name: ''
});

const showSuccess = ref(false);

const submit = () => {
    form.post(route('newsletter.subscribe'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            form.reset();
            showSuccess.value = true;
            setTimeout(() => {
                showSuccess.value = false;
            }, 5000);
        },
        onError: () => {
            // Handle errors gracefully
            console.error('Subscription error:', form.errors);
        }
    });
};
</script>

<template>
    <div class="newsletter-signup">
        <!-- Default variant -->
        <div v-if="variant === 'default'" class="bg-gray-50 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Subscribe to Our Newsletter</h3>
            <p class="text-sm text-gray-600 mb-4">Get the latest updates on our programs and initiatives delivered to your inbox.</p>
            
            <form @submit.prevent="submit" class="space-y-3">
                <div>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Your name (optional)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bloom-yellow-500 focus:border-transparent"
                        :disabled="form.processing"
                    />
                </div>

                <div>
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="Enter your email"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bloom-yellow-500 focus:border-transparent"
                        :class="{ 'border-red-500': form.errors.email }"
                        :disabled="form.processing"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                </div>
                
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full px-4 py-2 bg-bloom-yellow-500 text-white rounded-lg hover:bg-bloom-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bloom-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    <span v-if="form.processing">Subscribing...</span>
                    <span v-else>Subscribe</span>
                </button>
            </form>
            
            <p class="mt-3 text-xs text-gray-500">
                We respect your privacy. Unsubscribe at any time.
            </p>
        </div>
        
        <!-- Minimal variant -->
        <div v-else-if="variant === 'minimal'" class="max-w-md">
            <h4 class="text-lg font-semibold text-gray-900 mb-3">Stay Updated</h4>
            
            <form @submit.prevent="submit" class="flex gap-2">
                <input
                    v-model="form.email"
                    type="email"
                    placeholder="Your email address"
                    required
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-bloom-yellow-500 focus:border-transparent"
                    :class="{ 'border-red-500': form.errors.email }"
                    :disabled="form.processing"
                />
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-6 py-2 bg-bloom-yellow-500 text-white rounded-lg hover:bg-bloom-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bloom-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    Subscribe
                </button>
            </form>
            
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
        </div>
        
        <!-- Inline variant (for footer) -->
        <div v-else-if="variant === 'inline'" class="w-full max-w-md">
            <form @submit.prevent="submit" class="flex flex-col sm:flex-row gap-2">
                <input
                    v-model="form.email"
                    type="email"
                    placeholder="Enter your email"
                    required
                    class="flex-1 px-4 py-2 bg-gray-800 text-white placeholder-gray-400 border border-gray-700 rounded-lg focus:ring-2 focus:ring-bloom-yellow-500 focus:border-transparent"
                    :class="{ 'border-red-500': form.errors.email }"
                    :disabled="form.processing"
                />
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="px-4 py-2 bg-bloom-yellow-500 text-white text-sm border border-bloom-yellow-500 rounded-lg hover:bg-bloom-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-bloom-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors whitespace-nowrap"
                >
                    <span v-if="form.processing">Subscribing...</span>
                    <span v-else>Subscribe</span>
                </button>
            </form>
            <p v-if="form.errors.email" class="mt-1 text-sm text-red-300">{{ form.errors.email }}</p>
        </div>
        
        <!-- Success message -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div v-if="showSuccess" :class="[
                'mt-4 p-4 rounded-lg',
                variant === 'inline' 
                    ? 'bg-green-900/20 border border-green-500/30' 
                    : 'bg-green-50 border border-green-200'
            ]">
                <div class="flex">
                    <svg class="flex-shrink-0 h-5 w-5" :class="variant === 'inline' ? 'text-green-400' : 'text-green-400'" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    <div class="ml-3">
                        <p class="text-sm font-medium" :class="variant === 'inline' ? 'text-green-300' : 'text-green-800'">
                            Successfully subscribed! Check your email for confirmation.
                        </p>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>