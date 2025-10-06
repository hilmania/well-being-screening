<?php

namespace App\Console\Commands;

use App\Services\TopoplotService;
use Illuminate\Console\Command;

class ClearTopoplotCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'topoplot:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all topoplot image cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing topoplot cache...');

        try {
            TopoplotService::clearAllCache();
            $this->info('✅ Topoplot cache cleared successfully!');
        } catch (\Exception $e) {
            $this->error('❌ Error clearing topoplot cache: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
