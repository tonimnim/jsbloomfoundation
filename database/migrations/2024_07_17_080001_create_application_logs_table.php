<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('application_logs', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20); // emergency, alert, critical, error, warning, notice, info, debug
            $table->string('channel', 50)->default('app'); // auth, database, mail, etc.
            $table->text('message');
            $table->json('context')->nullable();
            $table->json('extra')->nullable();
            $table->string('file')->nullable();
            $table->integer('line')->nullable();
            $table->string('class')->nullable();
            $table->string('function')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();

            // Indexes for performance
            $table->index(['level', 'logged_at']);
            $table->index(['channel', 'logged_at']);
            $table->index(['logged_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_logs');
    }
};