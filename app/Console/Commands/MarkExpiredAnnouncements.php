<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkExpiredAnnouncements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mark-expired-announcements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Soft delete expired announcements automatically';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today();

        $expired = Announcement::whereNotNull('expires_at')
            ->where('expires_at', '<', $today)
            ->get();

        foreach ($expired as $announcement) {
            $announcement->delete();
        }

        $this->info("Soft deleted {$expired->count()} expired announcements.");
    }
}
