<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Deal;

class ClearDeals extends Command
{
    protected $signature = 'deals:clear';
    protected $description = 'Clear deals table at midnight';

    public function handle()
    {
        Deal::truncate(); // deletes all rows
        $this->info('Deals table cleared successfully.');
    }
}