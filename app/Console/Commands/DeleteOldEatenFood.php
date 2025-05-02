<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EatenFood; // Замените YourModel на вашу модель
use Carbon\Carbon;

class DeleteOldEatenFood extends Command
{
    protected $signature = 'records:delete-old-eaten-foods';
    protected $description = 'Delete eaten foods older than 30 days based on eaten_at field';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $thresholdDate = Carbon::today()->subDays(30);

        EatenFood::where('eaten_at', '<', $thresholdDate)->delete();

        $this->info('Old eaten foods deleted successfully.');
    }
}
