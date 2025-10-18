<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PackageDraft;

class CheckDraftImages extends Command
{
    protected $signature = 'debug:draft-images';
    protected $description = 'Check draft images status and URLs';

    public function handle()
    {
        $this->info('🔍 Checking Package Draft Images...');
        
        $drafts = PackageDraft::all();
        
        foreach ($drafts as $draft) {
            $images = $draft->draft_data['images'] ?? [];
            
            if (count($images) > 0) {
                $this->info("📋 Draft ID: {$draft->id}");
                $this->info("📊 Images Count: " . count($images));
                
                foreach ($images as $index => $image) {
                    $this->info("  🖼️  Image {$index}:");
                    $this->info("    ID: " . ($image['id'] ?? 'N/A'));
                    $this->info("    Filename: " . ($image['filename'] ?? 'N/A'));
                    $this->info("    Original Name: " . ($image['original_name'] ?? 'N/A'));
                    
                    if (isset($image['sizes'])) {
                        $this->info("    Sizes Available:");
                        foreach ($image['sizes'] as $size => $path) {
                            $fullPath = storage_path('app/public/' . ltrim($path, '/'));
                            $exists = file_exists($fullPath) ? '✅' : '❌';
                            $this->info("      {$size}: {$path} {$exists}");
                        }
                    }
                    
                    if (isset($image['url'])) {
                        $this->info("    URL: " . $image['url']);
                    }
                    
                    $this->info('');
                }
            }
        }
        
        $this->info('🔍 Checking recent files in storage...');
        
        $draftPath = storage_path('app/public/images/package-drafts');
        $packagePath = storage_path('app/public/images/packages');
        
        if (is_dir($draftPath)) {
            $draftFiles = glob($draftPath . '/**/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
            $this->info("📁 Draft images folder has: " . count($draftFiles) . " image files");
        }
        
        if (is_dir($packagePath)) {
            $packageFiles = glob($packagePath . '/**/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
            $this->info("📁 Package images folder has: " . count($packageFiles) . " image files");
        }
        
        return 0;
    }
}