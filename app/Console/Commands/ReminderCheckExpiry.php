<?php

namespace App\Console\Commands;

use App\Reminder;
use App\Jobs\SendExpiryReminders;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReminderCheckExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $reminders = Reminder::where(function ($query) {
            $query->where('status', '!=', 'completed')
                ->orWhereNull('status');
        })->get();

        foreach ($reminders as $reminder) {
            Log::info("reminder:" . $reminder);
            if ($reminder->expires_at && $reminder->expires_at->lt(now())) {
                $reminder->status = 'expired';
                $reminder->save();
                continue;
            }
            $nextReminderTime = $this->calculateNextReminderTime($reminder);

            Log::info("expired at:" . $reminder->expires_at);
            Log::info("nextReminderTime:" . $nextReminderTime);
            if ($nextReminderTime && $nextReminderTime->lte(now())) {
                SendExpiryReminders::dispatchNow($reminder);

                $reminder->last_reminder_at = now();
                if ($reminder->is_repeat && $reminder->repeat_end_type === 'endOccurrences') {
                    $reminder->decrement('repeat_end_occurrences');
                }
                $reminder->save();
            }
        }
    }

    private function calculateNextReminderTime(Reminder $reminder)
    {
        $expiryTime = $reminder->expires_at ? $reminder->expires_at->format('H:i:s') : null;

        $lastReminderTime = $reminder->last_reminder_at ?? $reminder->created_at;

        if ($reminder->is_repeat) {
            $nextReminderTime = Carbon::create($lastReminderTime);

            switch ($reminder->repeat_freq) {
                case 'year':
                    $nextReminderTime->addYears($reminder->repeat_interval);
                    break;
                case 'month':
                    $nextReminderTime->addMonths($reminder->repeat_interval);
                    break;
                case 'week':
                    $nextReminderTime->addWeeks($reminder->repeat_interval);
                    break;
                default:
                    $nextReminderTime->addDay();
                    break;
            }

            if ($expiryTime && $reminder->repeat_freq == 'daily') {
                $nextReminderTime->setTimeFromParts(explode(':', $expiryTime));
            }

            // Repeat end conditions check (moved after next reminder time calculation and expiry time adjustment)
            if (($reminder->repeat_end_type === 'endDate' && $nextReminderTime->gt($reminder->repeat_end_date)) ||
                ($reminder->repeat_end_type === 'endOccurrences' && $reminder->repeat_end_occurrences <= 0)
            ) {
                return null;
            }
        } else {
            $nextReminderTime = Carbon::parse($reminder->reminder_datetime);
        }


        return $nextReminderTime;
    }
}
