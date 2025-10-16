<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SetupStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup storage symlink and ensure directories exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create storage symlink
        $this->call('storage:link');
        
        // Ensure media directories exist
        $directories = [
            storage_path('app/public/images'),
            storage_path('app/public/videos'),
            storage_path('app/public/documents'),
            storage_path('app/public/homepage'),
        ];
        
        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->info("Created directory: " . $directory);
            }
        }
        
        $this->info('Storage setup completed successfully!');
        
        return 0;
    }
}