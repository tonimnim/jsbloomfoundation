<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EnsureStorageLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:ensure-link';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure storage link exists for Laravel Cloud';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $publicPath = public_path('storage');
        $storagePath = storage_path('app/public');

        if (!file_exists($publicPath)) {
            if (function_exists('symlink')) {
                symlink($storagePath, $publicPath);
                $this->info('Storage link created successfully.');
            } else {
                // If symlink is not available, try to create a junction (Windows) or copy files
                if (PHP_OS_FAMILY === 'Windows') {
                    exec("mklink /J \"$publicPath\" \"$storagePath\"");
                    $this->info('Storage junction created successfully.');
                } else {
                    $this->error('Unable to create storage link. Symlink function is disabled.');
                    return 1;
                }
            }
        } else {
            $this->info('Storage link already exists.');
        }

        // Ensure directories exist
        $directories = [
            storage_path('app/public/homepage'),
            storage_path('app/public/media'),
        ];

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                $this->info("Created directory: $dir");
            }
        }

        return 0;
    }
}