<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->foreignId('parent_id')->nullable()->constrained('media_folders')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index(['parent_id', 'sort_order']);
        });

        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->string('extension');
            $table->bigInteger('file_size'); // in bytes
            $table->json('dimensions')->nullable(); // width, height for images
            $table->text('alt_text')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('folder_id')->nullable()->constrained('media_folders')->onDelete('set null');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes for fast queries
            $table->index(['mime_type']);
            $table->index(['folder_id']);
            $table->index(['uploaded_by']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
        Schema::dropIfExists('media_folders');
    }
};