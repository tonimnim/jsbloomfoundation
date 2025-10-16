<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class MediaController extends Controller
{
    /**
     * Display the media library.
     */
    public function index(Request $request)
    {
        $query = \DB::table('media_files');
        
        // Filter by type
        if ($request->filled('type')) {
            if ($request->type === 'image') {
                $query->where('mime_type', 'like', 'image/%');
            } elseif ($request->type === 'video') {
                $query->where('mime_type', 'like', 'video/%');
            } elseif ($request->type === 'document') {
                $query->where('mime_type', 'not like', 'image/%')
                      ->where('mime_type', 'not like', 'video/%');
            }
        }
        
        // Search
        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('original_name', 'like', $search)
                    ->orWhere('alt_text', 'like', $search);
            });
        }
        
        $media = $query->orderBy('created_at', 'desc')
                      ->paginate(24)
                      ->withQueryString();
        
        // Add URL to each media item
        $media->through(function ($item) {
            $item->url = \Storage::disk('public')->url($item->file_path);
            $item->thumbnail_url = $item->thumbnail_path
                ? \Storage::disk('public')->url($item->thumbnail_path)
                : null;
            return $item;
        });
        
        return Inertia::render('Admin/Media/Index', [
            'media' => $media,
            'filters' => $request->only(['type', 'search']),
        ]);
    }
    
    /**
     * Upload new media files.
     */
    public function upload(Request $request)
    {
        // Check if it's a single file or multiple files
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            
            // Validate based on whether it's an array or single file
            if (is_array($files)) {
                $request->validate([
                    'files.*' => 'required|file|mimes:jpeg,jpg,png,gif,webp,mp4,webm,ogg,pdf,doc,docx|max:10240', // 10MB max
                ]);
            } else {
                // Single file upload
                $request->validate([
                    'files' => 'required|file|mimes:jpeg,jpg,png,gif,webp,mp4,webm,ogg,pdf,doc,docx|max:10240', // 10MB max
                ]);
                $files = [$files]; // Convert to array for uniform processing
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No files provided for upload.'
            ], 400);
        }
        
        $uploadedFiles = [];
        
        foreach ($files as $file) {
            try {
                // Generate unique filename
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                
                // Sanitize filename - remove any existing extensions and special chars
                $baseFilename = pathinfo($originalName, PATHINFO_FILENAME);
                $baseFilename = preg_replace('/[^a-zA-Z0-9-_]/', '-', $baseFilename);
                $baseFilename = Str::slug($baseFilename);
                
                $filename = $baseFilename . '-' . uniqid() . '.' . $extension;
                
                // Determine folder based on file type
                $mimeType = $file->getMimeType();
                if (str_starts_with($mimeType, 'image/')) {
                    $folder = 'images';
                } elseif (str_starts_with($mimeType, 'video/')) {
                    $folder = 'videos';
                } else {
                    $folder = 'documents';
                }
                
                $dimensions = null;
                $thumbnailPath = null;
                
                // Store file in public disk like gallery does
                $path = $file->storeAs($folder, $filename, 'public');
                
                // Get file size
                $fileSize = $file->getSize();
                
                // For images, try to get dimensions if possible
                if (str_starts_with($mimeType, 'image/')) {
                    $fullPath = storage_path('app/public/' . $path);
                    if (file_exists($fullPath)) {
                        $imageInfo = @getimagesize($fullPath);
                        if ($imageInfo) {
                            $dimensions = [
                                'width' => $imageInfo[0],
                                'height' => $imageInfo[1]
                            ];
                        }
                    }
                }
                
                // Save to database
                $mediaId = \DB::table('media_files')->insertGetId([
                    'name' => $filename,
                    'original_name' => $originalName,
                    'file_path' => $path,
                    'thumbnail_path' => $thumbnailPath,
                    'mime_type' => $mimeType,
                    'extension' => $extension,
                    'file_size' => $fileSize,
                    'dimensions' => $dimensions ? json_encode($dimensions) : null,
                    'uploaded_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Generate URL like gallery does
                $url = \Storage::disk('public')->url($path);

                $uploadedFiles[] = [
                    'id' => $mediaId,
                    'name' => $filename,
                    'url' => $url,
                    'thumbnail_url' => $thumbnailPath ? \Storage::disk('public')->url($thumbnailPath) : null,
                    'mime_type' => $mimeType,
                    'dimensions' => $dimensions,
                ];
                
            } catch (\Exception $e) {
                \Log::error('Media upload failed: ' . $e->getMessage(), [
                    'file' => $originalName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                continue; // Skip this file and continue with others
            }
        }
        
        return response()->json([
            'success' => true,
            'files' => $uploadedFiles,
            'message' => count($uploadedFiles) . ' file(s) uploaded successfully.',
        ]);
    }
    
    /**
     * Update media details.
     */
    public function update(Request $request, $id)
    {
        $media = \DB::table('media_files')->where('id', $id)->first();
        
        if (!$media) {
            abort(404);
        }
        
        $validated = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);
        
        \DB::table('media_files')->where('id', $id)->update([
            'alt_text' => $validated['alt_text'],
            'description' => $validated['description'],
            'updated_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Media updated successfully.');
    }
    
    /**
     * Delete media file.
     */
    public function destroy($id)
    {
        $media = \DB::table('media_files')->where('id', $id)->first();
        
        if (!$media) {
            abort(404);
        }
        
        // Delete file from storage
        Storage::disk('public')->delete($media->file_path);
        
        // Delete from database
        \DB::table('media_files')->where('id', $id)->delete();
        
        return redirect()->back()->with('success', 'Media deleted successfully.');
    }
    
    /**
     * Get all media for selection (used in modals).
     */
    public function all()
    {
        $media = \DB::table('media_files')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Add URL to each media item
        $media->transform(function ($item) {
            $item->url = \Storage::disk('public')->url($item->file_path);
            $item->thumbnail_url = $item->thumbnail_path
                ? \Storage::disk('public')->url($item->thumbnail_path)
                : null;
            return $item;
        });
        
        return response()->json($media);
    }
}