<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use App\Jobs\SendNewsletterForNewPost;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewBlogPostNotification;

class PostsController extends Controller
{
    /**
     * Display a listing of the posts.
     */
    public function index(Request $request): Response
    {
        $query = \DB::table('posts')
            ->join('post_categories', 'posts.category_id', '=', 'post_categories.id')
            ->join('users', 'posts.author_id', '=', 'users.id')
            ->select(
                'posts.*',
                'post_categories.name as category_name',
                'post_categories.color as category_color',
                'users.name as author_name'
            );

        // Search
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('posts.title', 'like', $search)
                    ->orWhere('posts.excerpt', 'like', $search)
                    ->orWhere('posts.content', 'like', $search);
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('posts.status', $request->status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('posts.category_id', $request->category);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy("posts.$sortField", $sortDirection);

        $posts = $query->paginate(10)->withQueryString();

        // Get categories for filter
        $categories = \DB::table('post_categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts,
            'categories' => $categories,
            'filters' => $request->only(['search', 'status', 'category']),
            'sort' => [
                'field' => $sortField,
                'direction' => $sortDirection,
            ],
        ]);
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(): Response
    {
        $categories = \DB::table('post_categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/Posts/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created post in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts|regex:/^[a-z0-9-]+$/',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string|min:10',
            'category_id' => 'required|exists:post_categories,id',
            'status' => 'required|in:draft,published',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'featured_image' => 'nullable|string|max:500',
            'meta_title' => 'nullable|string|max:60', // SEO best practice
            'meta_description' => 'nullable|string|max:160', // SEO best practice
            'meta_keywords' => 'nullable|string|max:255',
        ], [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            'content.min' => 'The content must be at least 10 characters long.',
            'meta_title.max' => 'The meta title should not exceed 60 characters for optimal SEO.',
            'meta_description.max' => 'The meta description should not exceed 160 characters for optimal SEO.',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            
            // Ensure unique slug
            $count = 1;
            $originalSlug = $validated['slug'];
            while (\DB::table('posts')->where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count;
                $count++;
            }
        }
        
        // Auto-generate excerpt if not provided
        if (empty($validated['excerpt'])) {
            $validated['excerpt'] = $this->generateExcerpt($validated['content']);
        }
        
        // Auto-generate meta description if not provided
        if (empty($validated['meta_description'])) {
            $validated['meta_description'] = $this->generateMetaDescription($validated['excerpt'] ?? $validated['content']);
        }

        // ALWAYS use server time when publishing
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        } else {
            $validated['published_at'] = null;
        }

        // Prepare meta data
        $metaData = [];
        if (!empty($validated['meta_title'])) {
            $metaData['title'] = $validated['meta_title'];
        }
        if (!empty($validated['meta_description'])) {
            $metaData['description'] = $validated['meta_description'];
        }
        if (!empty($validated['meta_keywords'])) {
            $metaData['keywords'] = $validated['meta_keywords'];
        }

        // Insert post
        $postId = \DB::table('posts')->insertGetId([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'category_id' => $validated['category_id'],
            'status' => $validated['status'],
            'is_featured' => $validated['is_featured'] ?? false,
            'allow_comments' => $validated['allow_comments'] ?? true,
            'published_at' => $validated['published_at'],
            'featured_image' => $validated['featured_image'],
            'meta_data' => !empty($metaData) ? json_encode($metaData) : null,
            'author_id' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send newsletter notification if post is published
        if ($validated['status'] === 'published') {
            $this->sendNewsletterNotification($postId);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post created successfully.');
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit($id): Response
    {
        $post = \DB::table('posts')->where('id', $id)->first();
        
        if (!$post) {
            abort(404);
        }

        // Decode meta data
        if ($post->meta_data) {
            $metaData = json_decode($post->meta_data, true);
            $post->meta_title = $metaData['title'] ?? '';
            $post->meta_description = $metaData['description'] ?? '';
            $post->meta_keywords = $metaData['keywords'] ?? '';
        }

        $categories = \DB::table('post_categories')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/Posts/Edit', [
            'post' => $post,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified post in storage.
     */
    public function update(Request $request, $id)
    {
        $post = \DB::table('posts')->where('id', $id)->first();
        
        if (!$post) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,' . $id,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'category_id' => 'required|exists:post_categories,id',
            'status' => 'required|in:draft,published',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'featured_image' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            
            // Ensure unique slug
            $count = 1;
            $originalSlug = $validated['slug'];
            while (\DB::table('posts')->where('slug', $validated['slug'])->where('id', '!=', $id)->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count;
                $count++;
            }
        }

        // ALWAYS use server time when publishing
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        } else {
            $validated['published_at'] = null;
        }

        // Prepare meta data
        $metaData = [];
        if (!empty($validated['meta_title'])) {
            $metaData['title'] = $validated['meta_title'];
        }
        if (!empty($validated['meta_description'])) {
            $metaData['description'] = $validated['meta_description'];
        }
        if (!empty($validated['meta_keywords'])) {
            $metaData['keywords'] = $validated['meta_keywords'];
        }

        // Check if post is being published for the first time
        $wasPublished = $post->status === 'published';
        $isNowPublished = $validated['status'] === 'published';
        
        // Update post
        \DB::table('posts')->where('id', $id)->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'],
            'content' => $validated['content'],
            'category_id' => $validated['category_id'],
            'status' => $validated['status'],
            'is_featured' => $validated['is_featured'] ?? false,
            'allow_comments' => $validated['allow_comments'] ?? true,
            'published_at' => $validated['published_at'],
            'featured_image' => $validated['featured_image'],
            'meta_data' => !empty($metaData) ? json_encode($metaData) : null,
            'updated_at' => now(),
        ]);
        
        // Send newsletter notification if post is newly published
        if (!$wasPublished && $isNowPublished) {
            $this->sendNewsletterNotification($id);
        }

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post updated successfully.');
    }

    /**
     * Remove the specified post from storage.
     */
    public function destroy($id)
    {
        $post = \DB::table('posts')->where('id', $id)->first();
        
        if (!$post) {
            abort(404);
        }

        \DB::table('posts')->where('id', $id)->delete();

        return redirect()->route('admin.posts.index')
            ->with('success', 'Post deleted successfully.');
    }

    /**
     * Toggle featured status of a post.
     */
    public function toggleFeatured($id)
    {
        $post = \DB::table('posts')->where('id', $id)->first();
        
        if (!$post) {
            abort(404);
        }

        \DB::table('posts')->where('id', $id)->update([
            'is_featured' => !$post->is_featured,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Featured status updated.');
    }

    /**
     * Manage post categories.
     */
    public function categories(): Response
    {
        $categories = \DB::table('post_categories')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('Admin/Posts/Categories', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a new category.
     */
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:post_categories',
            'description' => 'nullable|string|max:500',
            'color' => 'nullable|string|max:7',
            'sort_order' => 'nullable|integer',
        ]);

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        \DB::table('post_categories')->insert([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'],
            'color' => $validated['color'] ?? '#3B82F6',
            'sort_order' => $validated['sort_order'] ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Category created successfully.');
    }

    /**
     * Process content for additional features.
     */
    private function processContent($content)
    {
        // Remove HTML tags for processing
        $plainText = strip_tags($content);
        
        // Clean up extra whitespace
        $plainText = preg_replace('/\s+/', ' ', $plainText);
        
        return trim($plainText);
    }
    
    /**
     * Generate excerpt from content.
     */
    private function generateExcerpt($content, $length = 150)
    {
        $plainText = $this->processContent($content);
        
        if (strlen($plainText) <= $length) {
            return $plainText;
        }
        
        // Find the last complete word within the length limit
        $excerpt = substr($plainText, 0, $length);
        $lastSpace = strrpos($excerpt, ' ');
        
        if ($lastSpace !== false) {
            $excerpt = substr($excerpt, 0, $lastSpace);
        }
        
        return $excerpt . '...';
    }
    
    /**
     * Generate meta description from content.
     */
    private function generateMetaDescription($content, $length = 160)
    {
        $plainText = $this->processContent($content);
        
        if (strlen($plainText) <= $length) {
            return $plainText;
        }
        
        // Find the last complete word within the length limit
        $description = substr($plainText, 0, $length);
        $lastSpace = strrpos($description, ' ');
        
        if ($lastSpace !== false) {
            $description = substr($description, 0, $lastSpace);
        }
        
        return $description . '...';
    }

    /**
     * Send newsletter notification for new blog post.
     */
    private function sendNewsletterNotification($postId)
    {
        // Get the post with category and author info
        $post = \DB::table('posts')
            ->join('post_categories', 'posts.category_id', '=', 'post_categories.id')
            ->join('users', 'posts.author_id', '=', 'users.id')
            ->select(
                'posts.*',
                'post_categories.name as category_name',
                'users.name as author_name'
            )
            ->where('posts.id', $postId)
            ->first();
        
        if (!$post) return;

        // Get active newsletter subscribers
        $subscribers = \DB::table('newsletter_subscribers')
            ->where('is_active', true)
            ->get();

        // Dispatch job to send notifications
        if ($subscribers->count() > 0) {
            SendNewsletterForNewPost::dispatch($post);
        }
    }
}