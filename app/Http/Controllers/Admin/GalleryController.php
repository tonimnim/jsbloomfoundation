<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GalleryController extends Controller
{
    /**
     * Display all gallery images.
     */
    public function index(): Response
    {
        $images = DB::table('gallery_images')
            ->orderByDesc('created_at')
            ->paginate(20);

        $images->getCollection()->transform(function ($image) {
            if (!str_starts_with($image->url, 'https://')) {
                $image->url = Storage::disk('public')->url($image->path);
            }
            return $image;
        });

        return Inertia::render('Admin/Gallery/Index', [
            'images' => $images,
        ]);
    }

    /**
     * Store a newly uploaded image.
     */
    public function store(Request $request): RedirectResponse
    {
        set_time_limit(120);
        
        $validated = $request->validate([
            'images' => 'required|array',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:20480',
        ]);

        try {
            foreach ($request->file('images') as $uploadedFile) {
                $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
                $path = $uploadedFile->storeAs('gallery', $filename, 'public');
                $url = Storage::disk('public')->url($path);
                
                DB::table('gallery_images')->insert([
                    'filename' => $filename,
                    'path' => $path,
                    'url' => $url,
                    'size' => $uploadedFile->getSize(),
                    'mime_type' => $uploadedFile->getMimeType(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return redirect()->route('admin.gallery.index')
                ->with('success', 'Images uploaded successfully!');
                
        } catch (\Exception $e) {
            Log::error('Gallery upload error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to upload images. Please try again.');
        }
    }

    /**
     * Delete an image from gallery.
     */
    public function destroy($id): RedirectResponse
    {
        $image = DB::table('gallery_images')->find($id);

        if (!$image) {
            abort(404);
        }

        try {
            if (Storage::disk('public')->exists($image->path)) {
                Storage::disk('public')->delete($image->path);
            }

            DB::table('gallery_images')->where('id', $id)->delete();

            return redirect()->route('admin.gallery.index')
                ->with('success', 'Image deleted successfully!');
                
        } catch (\Exception $e) {
            Log::error('Gallery delete error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to delete image. Please try again.');
        }
    }
}