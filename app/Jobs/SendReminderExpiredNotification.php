<?php

namespace App\Jobs;

use App\Mail\ReminderExpired;
use App\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReminderExpiredNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $reminder;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $emails = $this->reminder->emails;
        if (is_string($emails)) {  // Check if it's a JSON string
            $emails = json_decode($emails, true);
        }

        if ($emails && is_array($emails)) {
            foreach ($emails as $email) {
                Mail::to($email)->send(new ReminderExpired($this->reminder));
            }
        } else {
            // Handle the case where emails is not a valid array or is empty.
            // Log an error, send to a default email, or take other appropriate action.
            Log::error('Invalid or missing email addresses for reminder: ' . $this->reminder->id);
        }
    }
}
