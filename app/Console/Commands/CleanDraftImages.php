<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PackageDraft;

class CleanDraftImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-draft-images {--draft-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up empty images from draft data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $draftId = $this->option('draft-id');
        
        if ($draftId) {
            $draft = PackageDraft::find($draftId);
            if (!$draft) {
                $this->error("Draft with ID {$draftId} not found.");
                return;
            }
            $drafts = [$draft];
        } else {
            $drafts = PackageDraft::all();
        }
        
        foreach ($drafts as $draft) {
            $images = $draft->draft_data['images'] ?? [];
            $originalCount = count($images);
            
            $validImages = array_filter($images, function($img) {
                return !empty($img) && isset($img['id']) && !empty($img['id']);
            });
            
            $cleanCount = count($validImages);
            
            if ($originalCount !== $cleanCount) {
                $draftData = $draft->draft_data;
                $draftData['images'] = array_values($validImages);
                $draft->draft_data = $draftData;
                $draft->save();
                
                $this->info("Draft {$draft->id}: Cleaned {$originalCount} -> {$cleanCount} images");
            } else {
                $this->line("Draft {$draft->id}: No cleaning needed ({$cleanCount} images)");
            }
        }
        
        $this->info('Draft images cleanup completed.');
    }
}
