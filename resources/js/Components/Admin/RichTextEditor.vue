<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import Youtube from '@tiptap/extension-youtube';
import { watch, onBeforeUnmount, ref, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    placeholder: {
        type: String,
        default: 'Start writing your content...',
    },
    autoSave: {
        type: Boolean,
        default: true,
    },
    autoSaveDelay: {
        type: Number,
        default: 2000, // 2 seconds
    },
    autoSaveKey: {
        type: String,
        default: 'blog-post-draft',
    },
});

const emit = defineEmits(['update:modelValue', 'auto-save']);

// Auto-save state
const autoSaveTimer = ref(null);
const lastSavedContent = ref('');
const autoSaveStatus = ref('saved'); // 'saved', 'saving', 'unsaved'

const editor = useEditor({
    content: props.modelValue,
    extensions: [
        StarterKit.configure({
            heading: {
                levels: [1, 2, 3, 4],
            },
        }),
        Image.configure({
            inline: true,
            allowBase64: true,
        }),
        Youtube.configure({
            width: 640,
            height: 480,
        }),
    ],
    editorProps: {
        attributes: {
            class: 'prose prose-sm sm:prose lg:prose-lg xl:prose-xl max-w-none focus:outline-none min-h-[400px] px-4 py-3',
            'data-placeholder': props.placeholder,
        },
    },
    onUpdate: ({ editor }) => {
        const content = editor.getHTML();
        emit('update:modelValue', content);
        
        // Handle auto-save
        if (props.autoSave) {
            handleAutoSave(content);
        }
    },
});

// Watch for external changes
watch(() => props.modelValue, (value) => {
    if (editor.value && value !== editor.value.getHTML()) {
        editor.value.commands.setContent(value, false);
    }
});

// Auto-save functionality
const handleAutoSave = (content) => {
    if (content === lastSavedContent.value) return;
    
    autoSaveStatus.value = 'unsaved';
    
    // Clear existing timer
    if (autoSaveTimer.value) {
        clearTimeout(autoSaveTimer.value);
    }
    
    // Set new timer
    autoSaveTimer.value = setTimeout(() => {
        performAutoSave(content);
    }, props.autoSaveDelay);
};

const performAutoSave = async (content) => {
    try {
        autoSaveStatus.value = 'saving';
        
        // Save to localStorage as fallback
        localStorage.setItem(props.autoSaveKey, content);
        
        // Emit auto-save event for parent component to handle
        emit('auto-save', {
            content,
            timestamp: new Date().toISOString(),
        });
        
        lastSavedContent.value = content;
        autoSaveStatus.value = 'saved';
    } catch (error) {
        console.error('Auto-save failed:', error);
        autoSaveStatus.value = 'unsaved';
    }
};

// Load saved content on mount
onMounted(() => {
    if (props.autoSave && !props.modelValue) {
        const savedContent = localStorage.getItem(props.autoSaveKey);
        if (savedContent && savedContent.trim() !== '' && savedContent !== '<p></p>') {
            editor.value?.commands.setContent(savedContent);
            emit('update:modelValue', savedContent);
        }
    }
    lastSavedContent.value = props.modelValue;
});

// Cleanup
onBeforeUnmount(() => {
    if (autoSaveTimer.value) {
        clearTimeout(autoSaveTimer.value);
    }
    if (editor.value) {
        editor.value.destroy();
    }
});

// Hidden file input refs
const imageInput = ref(null);
const videoInput = ref(null);

// Editor commands
const toggleBold = () => editor.value.chain().focus().toggleBold().run();
const toggleItalic = () => editor.value.chain().focus().toggleItalic().run();
const toggleStrike = () => editor.value.chain().focus().toggleStrike().run();
const toggleCode = () => editor.value.chain().focus().toggleCode().run();
const toggleBulletList = () => editor.value.chain().focus().toggleBulletList().run();
const toggleOrderedList = () => editor.value.chain().focus().toggleOrderedList().run();
const toggleBlockquote = () => editor.value.chain().focus().toggleBlockquote().run();
const setHorizontalRule = () => editor.value.chain().focus().setHorizontalRule().run();
const undo = () => editor.value.chain().focus().undo().run();
const redo = () => editor.value.chain().focus().redo().run();

const setHeading = (level) => {
    if (level === 0) {
        editor.value.chain().focus().setParagraph().run();
    } else {
        editor.value.chain().focus().toggleHeading({ level }).run();
    }
};

const setLink = () => {
    const previousUrl = editor.value.getAttributes('link').href;
    const url = window.prompt('URL', previousUrl);

    if (url === null) return;

    if (url === '') {
        editor.value.chain().focus().extendMarkRange('link').unsetLink().run();
    } else {
        editor.value.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
    }
};

// Handle image upload from device
const addImage = () => {
    imageInput.value.click();
};

const handleImageUpload = async (event) => {
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
        formData.append('files[0]', file);
        
        const response = await axios.post('/admin/media/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        });
        
        if (response.data && response.data.files && response.data.files[0]) {
            const imageUrl = response.data.files[0].url;
            editor.value.chain().focus().setImage({ src: imageUrl }).run();
        }
    } catch (error) {
        console.error('Image upload failed:', error);
        alert('Failed to upload image. Please try again.');
    }
    
    // Reset input
    event.target.value = '';
};

const addImageUrl = (url) => {
    if (url) {
        editor.value.chain().focus().setImage({ src: url }).run();
    }
};

// Handle video upload from device
const addVideo = () => {
    videoInput.value.click();
};

const handleVideoUpload = async (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    // Validate file type
    if (!file.type.startsWith('video/')) {
        alert('Please select a video file');
        return;
    }
    
    // Validate file size (max 50MB)
    if (file.size > 50 * 1024 * 1024) {
        alert('Video size must be less than 50MB');
        return;
    }
    
    try {
        // Upload to server
        const formData = new FormData();
        formData.append('files[0]', file);
        
        const response = await axios.post('/admin/media/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        });
        
        if (response.data && response.data.files && response.data.files[0]) {
            const videoUrl = response.data.files[0].url;
            // Insert video as HTML5 video element
            editor.value.chain().focus().insertContent(`
                <video controls style="width: 100%; max-width: 640px; margin: 1rem 0;">
                    <source src="${videoUrl}" type="${file.type}">
                    Your browser does not support the video tag.
                </video>
            `).run();
        }
    } catch (error) {
        console.error('Video upload failed:', error);
        alert('Failed to upload video. Please try again.');
    }
    
    // Reset input
    event.target.value = '';
};

const addYoutubeVideo = () => {
    const url = window.prompt('Enter YouTube URL');
    if (url) {
        editor.value.commands.setYoutubeVideo({
            src: url,
        });
    }
};

// Expose method to parent
defineExpose({
    addImageUrl,
});
</script>

<template>
    <div class="border border-gray-300 rounded-lg overflow-hidden">
        <!-- Toolbar -->
        <div class="bg-gray-50 border-b border-gray-300 p-2 flex flex-wrap gap-1">
            <!-- Text Format -->
            <div class="flex items-center gap-1 px-2 border-r border-gray-300">
                <select 
                    @change="setHeading(parseInt($event.target.value))"
                    class="text-sm border border-gray-300 rounded px-2 py-1 focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                >
                    <option value="0">Paragraph</option>
                    <option value="1">Heading 1</option>
                    <option value="2">Heading 2</option>
                    <option value="3">Heading 3</option>
                    <option value="4">Heading 4</option>
                </select>
            </div>

            <!-- Text Styling -->
            <div class="flex items-center gap-1 px-2 border-r border-gray-300">
                <button
                    @click="toggleBold"
                    :class="{ 'bg-gray-200': editor?.isActive('bold') }"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors"
                    title="Bold"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h9a4 4 0 014 4 4 4 0 01-4 4H6z"></path>
                    </svg>
                </button>
                <button
                    @click="toggleItalic"
                    :class="{ 'bg-gray-200': editor?.isActive('italic') }"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors"
                    title="Italic"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 4h4M14 4l-4 16m-2 0h4"></path>
                    </svg>
                </button>
                <button
                    @click="toggleStrike"
                    :class="{ 'bg-gray-200': editor?.isActive('strike') }"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors"
                    title="Strikethrough"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12h12M12 12H0m7-7l5 14"></path>
                    </svg>
                </button>
                <button
                    @click="toggleCode"
                    :class="{ 'bg-gray-200': editor?.isActive('code') }"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors"
                    title="Code"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
                </button>
            </div>

            <!-- Lists -->
            <div class="flex items-center gap-1 px-2 border-r border-gray-300">
                <button
                    @click="toggleBulletList"
                    :class="{ 'bg-gray-200': editor?.isActive('bulletList') }"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors"
                    title="Bullet List"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6h13M8 12h13m-13 6h13M3 6h.01M3 12h.01M3 18h.01"></path>
                    </svg>
                </button>
                <button
                    @click="toggleOrderedList"
                    :class="{ 'bg-gray-200': editor?.isActive('orderedList') }"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors"
                    title="Numbered List"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20h14M7 10h14M7 6h2m-2 8h2m-2 8h2M3 4v2m0-2v2m0 6v2m0-2v2m0 6v2m0-2v2"></path>
                    </svg>
                </button>
            </div>

            <!-- Insert -->
            <div class="flex items-center gap-1 px-2 border-r border-gray-300">
                <button
                    @click="setLink"
                    :class="{ 'bg-gray-200': editor?.isActive('link') }"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors"
                    title="Add Link"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                </button>
                <button
                    @click="addImage"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors"
                    title="Add Image from Device"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </button>
                <button
                    @click="addVideo"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors"
                    title="Add Video from Device"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                </button>
                <button
                    @click="addYoutubeVideo"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors"
                    title="Add YouTube Video"
                >
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                </button>
                <button
                    @click="toggleBlockquote"
                    :class="{ 'bg-gray-200': editor?.isActive('blockquote') }"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors"
                    title="Blockquote"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                    </svg>
                </button>
                <button
                    @click="setHorizontalRule"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors"
                    title="Horizontal Rule"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h18"></path>
                    </svg>
                </button>
            </div>

            <!-- History -->
            <div class="flex items-center gap-1 px-2">
                <button
                    @click="undo"
                    :disabled="!editor?.can().undo()"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    title="Undo"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                </button>
                <button
                    @click="redo"
                    :disabled="!editor?.can().redo()"
                    class="p-1.5 rounded hover:bg-gray-200 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    title="Redo"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2m18-10l-6 6m6-6l-6-6"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Editor Content -->
        <div class="bg-white relative">
            <EditorContent :editor="editor" />
            
            <!-- Auto-save Status -->
            <div v-if="autoSave" class="absolute bottom-2 right-2 flex items-center gap-2 text-xs text-gray-500 bg-white px-2 py-1 rounded shadow-sm border">
                <div class="flex items-center gap-1">
                    <!-- Saving indicator -->
                    <svg v-if="autoSaveStatus === 'saving'" class="w-3 h-3 animate-spin text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <!-- Saved indicator -->
                    <svg v-else-if="autoSaveStatus === 'saved'" class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <!-- Unsaved indicator -->
                    <svg v-else class="w-3 h-3 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>
                <span>
                    {{ autoSaveStatus === 'saving' ? 'Saving...' : autoSaveStatus === 'saved' ? 'Saved' : 'Unsaved changes' }}
                </span>
            </div>
        </div>
    </div>
    
    <!-- Hidden file inputs -->
    <input
        ref="imageInput"
        type="file"
        accept="image/*"
        class="hidden"
        @change="handleImageUpload"
    />
    <input
        ref="videoInput"
        type="file"
        accept="video/*"
        class="hidden"
        @change="handleVideoUpload"
    />
</template>

<style>
/* Basic editor styles */
.ProseMirror {
    min-height: 400px;
}

.ProseMirror:focus {
    outline: none;
}

/* Placeholder */
.ProseMirror p.is-editor-empty:first-child::before {
    content: attr(data-placeholder);
    float: left;
    color: #adb5bd;
    pointer-events: none;
    height: 0;
}

/* YouTube embed styles */
.ProseMirror iframe {
    max-width: 100%;
    margin: 1rem 0;
}
</style>