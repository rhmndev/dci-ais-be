<?php

namespace App\Console\Commands;

use App\Jobs\SendReminderExpiredNotification;
use App\Reminder;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NotifyExpiredReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:notify-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify users about expired reminders.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::info('Processing expired reminder notifications...');
        $expiredReminders = Reminder::where(function ($query) {
            $query->where('expires_at', '<', Carbon::now())
                ->where('status', '!=', 'completed');
        })
            ->orWhereNull('status')
            ->get();
        Log::info("expiredReminders:" . $expiredReminders);

        foreach ($expiredReminders as $reminder) {
            try {
                // Dispatch the notification job for each expired reminder
                SendReminderExpiredNotification::dispatchNow($reminder);

                Log::info("Expired reminder notification queued for reminder ID: " . $reminder->_id);
            } catch (\Exception $e) {
                Log::error("Error queuing notification for reminder ID " . $reminder->_id . ": " . $e->getMessage());
            }
        }

        $this->info('Expired reminder notifications processed.');
        return 0;
    }
}
