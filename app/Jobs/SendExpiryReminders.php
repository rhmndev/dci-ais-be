<?php

namespace App\Jobs;

use App\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\FileExpiryReminder;
use App\User;
use Illuminate\Support\Facades\Notification;


class SendExpiryReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $files = File::where('notify_expiry', true)
            ->where('remind_at', '<=', now()) // Check remind_at
            ->get();
        foreach ($files as $file) {
            $user = User::find($file->user_id); // Get the user

            if ($file->notification_method === 'email' || $file->notification_method === 'both') {
                Notification::send($user, new FileExpiryReminder($file));  // Send email notification
            }

            if ($file->notification_method === 'whatsapp' || $file->notification_method === 'both') {
                if ($file->whatsapp_number) {
                    // Send WhatsApp notification using your preferred library (e.g., Twilio)
                    // Example (Twilio):
                    // $message = "Reminder: File '{$file->name}' is expiring soon!";
                    // // Your Twilio logic to send the WhatsApp message
                    // // ...
                }
            }

            // Update remind_at if necessary (e.g., remind again in a week)
            if ($file->remind_me_later) { // Only update if remind_me_later is true
                $file->remind_at = now()->addDays(7); // Or your desired interval
                $file->save();
            }
        }
    }
}
