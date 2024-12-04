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

        Log::info("last reminder at:" . $lastReminderTime);
        if ($reminder->is_repeat) {
            $nextReminderTime = Carbon::create($lastReminderTime);

            switch ($reminder->repeat_freq) {
                case 'year':
                    $nextReminderTime->addYears($reminder->repeat_interval);
                    break;
                case 'month':
                    $nextReminderTime->addMonths($reminder->repeat_interval);
                    $originalDayOfMonth = $lastReminderTime->day; // Get the original day of the month from $lastReminderTime. If null/blank, it should be from create_at
                    $newDayOfMonth = $nextReminderTime->day;
                    Log::info("originalDayOfMonth:" . $originalDayOfMonth);
                    Log::info("newDayOfMonth:" . $newDayOfMonth);
                    if ($originalDayOfMonth !== $newDayOfMonth) {
                        // Try to set the day of the month to the original day.
                        try {
                            $nextReminderTime->day($originalDayOfMonth);
                        } catch (\Exception $e) {
                            // If the original day is invalid for the next month (e.g., 31st in February),
                            // handle the exception (use last day of month, first day, or other logic).
                            $nextReminderTime->lastOfMonth(); // Example: set to the last day of the month.
                        }
                    }
                    break;
                case 'week':
                    $daysOfWeek = $reminder->repeat_day_of_week; // Retrieve the array of days of the week
                    if ($daysOfWeek && is_array($daysOfWeek)) {
                        $currentDayOfWeek = $nextReminderTime->dayOfWeek; //0 (Sunday) to 6 (Saturday)
                        $daysToAdd = 7;

                        foreach ($daysOfWeek as $dayOfWeek) {
                            $targetDayOfWeek = Carbon::parse($dayOfWeek)->dayOfWeek; // Assuming $dayOfWeek is something like "Monday"

                            if ($targetDayOfWeek > $currentDayOfWeek) {

                                $daysToAdd = $targetDayOfWeek - $currentDayOfWeek;
                                break;
                            }

                            if ($targetDayOfWeek < $currentDayOfWeek) {
                                $daysToAdd = 7 - ($currentDayOfWeek - $targetDayOfWeek);
                            }

                            if ($targetDayOfWeek == $currentDayOfWeek) {
                                Log::info("last reminder for weekly today:" . $lastReminderTime);
                                $daysToAdd = 0;
                                if ($lastReminderTime->isToday() && $expiryTime && Carbon::createFromTimeString($expiryTime)->lt(now())) {
                                    $nextAvailableDay = $currentDayOfWeek + 1; // Start from the next day
                                    while (!in_array(Carbon::now()->addDays($nextAvailableDay - $currentDayOfWeek)->format('l'), $daysOfWeek)) {
                                        $nextAvailableDay++;
                                        if ($nextAvailableDay > 6) { // Reset to 0 (Sunday) if we go past Saturday
                                            $nextAvailableDay = 0;
                                        }

                                        if ($nextAvailableDay == $currentDayOfWeek) {
                                            // If we cycled back to the original day, it means there are no other days of the week
                                            // in $daysOfWeek, so add 7 days and skip to next week.
                                            $daysToAdd = 7;
                                            break;
                                        }
                                    }
                                    $daysToAdd = ($nextAvailableDay > $currentDayOfWeek)
                                        ? $nextAvailableDay - $currentDayOfWeek
                                        : 7 - $currentDayOfWeek + $nextAvailableDay;
                                }
                                break;
                            }
                        }
                        $nextReminderTime->addDays($daysToAdd);
                    }
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
