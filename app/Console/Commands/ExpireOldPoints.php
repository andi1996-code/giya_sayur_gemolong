<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\PointHelper;

class ExpireOldPoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'points:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire old member points that have passed their expiry date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to expire old points...');

        $expiredCount = PointHelper::expireOldPoints();

        if ($expiredCount > 0) {
            $this->info("Successfully expired {$expiredCount} point transaction(s).");
        } else {
            $this->info('No points to expire.');
        }

        return Command::SUCCESS;
    }
}
