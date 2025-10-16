<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('brief_description');
            $table->longText('full_description');
            $table->longText('objectives')->nullable();
            $table->longText('activities')->nullable();
            $table->string('featured_image')->nullable();
            $table->json('gallery')->nullable(); // project images
            $table->json('videos')->nullable(); // video URLs/embeds
            $table->decimal('funding_goal', 15, 2)->nullable();
            $table->decimal('current_funding', 15, 2)->default(0);
            $table->string('status')->default('active'); // active, completed, paused, archived
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('counties')->nullable(); // counties where project operates
            $table->integer('sort_order')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('accepts_donations')->default(true);
            $table->timestamps();
            
            // Indexes for fast queries
            $table->index(['status', 'is_featured']);
            $table->index(['accepts_donations', 'status']);
            $table->index(['slug']);
            
            // Conditional fulltext index (only for MySQL/PostgreSQL)
            if (config('database.default') !== 'sqlite') {
                $table->fullText(['name', 'brief_description']); // Search optimization
            }
        });

        Schema::create('impact_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Farmers Trained", "Trees Planted", etc.
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('unit'); // "people", "trees", "hectares", etc.
            $table->string('icon')->nullable(); // FontAwesome icon class
            $table->string('color', 7)->default('#10B981'); // hex color
            $table->boolean('is_cumulative')->default(true); // true for running totals
            $table->integer('current_value')->default(0);
            $table->integer('target_value')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('show_on_homepage')->default(false);
            $table->timestamps();
            
            $table->index(['is_active', 'show_on_homepage']);
            $table->index(['sort_order']);
        });

        Schema::create('project_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('metric_id')->constrained('impact_metrics')->onDelete('cascade');
            $table->integer('value')->default(0);
            $table->integer('target')->nullable();
            $table->timestamps();
            
            // Composite index for fast project metric queries
            $table->index(['project_id', 'metric_id']);
            $table->unique(['project_id', 'metric_id']);
        });

        Schema::create('metric_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('metric_id')->constrained('impact_metrics')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->integer('old_value');
            $table->integer('new_value');
            $table->integer('change_amount');
            $table->text('note')->nullable();
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            // Indexes for audit trail and reporting
            $table->index(['metric_id', 'created_at']);
            $table->index(['project_id', 'created_at']);
            $table->index(['updated_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metric_updates');
        Schema::dropIfExists('project_metrics');
        Schema::dropIfExists('impact_metrics');
        Schema::dropIfExists('projects');
    }
};