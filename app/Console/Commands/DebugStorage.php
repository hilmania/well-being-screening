<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DebugStorage extends Command
{
    protected $signature = 'debug:storage {filename?}';
    protected $description = 'Debug storage issues for volunteer attachments';

    public function handle()
    {
        $filename = $this->argument('filename');

        $this->info('=== Storage Debug Information ===');

        // 1. Check storage configuration
        $this->info('1. Storage Configuration:');
        $this->line('Public disk path: ' . Storage::disk('public')->path(''));
        $this->line('Storage path: ' . storage_path('app/public/'));
        $this->line('Public path: ' . public_path('storage/'));

        // 2. Check symbolic link
        $this->info('2. Symbolic Link Status:');
        $linkExists = is_link(public_path('storage'));
        $this->line('Link exists: ' . ($linkExists ? 'YES' : 'NO'));
        if ($linkExists) {
            $this->line('Link target: ' . readlink(public_path('storage')));
        }

        // 3. Check volunteer attachments directory
        $this->info('3. Volunteer Attachments Directory:');
        $attachmentsPath = 'volunteer-attachments';
        $exists = Storage::disk('public')->exists($attachmentsPath);
        $this->line('Directory exists: ' . ($exists ? 'YES' : 'NO'));

        if ($exists) {
            $files = Storage::disk('public')->files($attachmentsPath);
            $this->line('Total files: ' . count($files));

            if ($filename) {
                $filePath = $attachmentsPath . '/' . $filename;
                $fileExists = Storage::disk('public')->exists($filePath);
                $this->info('4. Specific File Check:');
                $this->line('File: ' . $filename);
                $this->line('Exists: ' . ($fileExists ? 'YES' : 'NO'));

                if ($fileExists) {
                    $fullPath = Storage::disk('public')->path($filePath);
                    $this->line('Full path: ' . $fullPath);
                    $this->line('File readable: ' . (is_readable($fullPath) ? 'YES' : 'NO'));
                    $this->line('File size: ' . Storage::disk('public')->size($filePath) . ' bytes');
                }
            } else {
                $this->line('Recent files:');
                foreach (array_slice($files, -5) as $file) {
                    $this->line('  - ' . basename($file));
                }
            }
        }

        // 4. Generate test URL
        if ($filename) {
            $this->info('5. Test URLs:');
            $this->line('Asset URL: ' . asset('storage/volunteer-attachments/' . $filename));
            $this->line('Route URL: ' . route('download.volunteer-attachment', ['filename' => $filename]));
        }

        $this->info('=== End Debug ===');
    }
}
