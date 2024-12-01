<?php

namespace App\Jobs;

use App\Reminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\FileExpiryReminder;
use App\Notifications\ReminderNotification; // Assuming you have this notification class
use Illuminate\Support\Facades\Log;
use App\User;
use Illuminate\Support\Facades\Notification;
use Twilio\Rest\Client;

class SendExpiryReminders implements ShouldQueue
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
        $reminder = $this->reminder;
        $user = User::find($reminder->user_id);

        if (!$user) {
            $user = User::where('username', $reminder->username)->first(); // Try finding by username
            if (!$user) {
                // Handle the case where the user is not found by either ID or username
                Log::error("User not found for reminder ID: " . $reminder->id . " and username: " . $reminder->username);
                return; // Or throw an exception, depending on your error handling strategy
            }
        }

        Log::info("Sending reminder for ID: " . $reminder->id . " to user: " . $user->username);

        if ($reminder->reminder_method === 'email' || $reminder->reminder_method === 'both') {
            if ($reminder->emails && is_array($reminder->emails)) { // Check if emails exist and is an array
                foreach ($reminder->emails as $email) {
                    Notification::route('mail', $email)->notify(new ReminderNotification($reminder));
                }
            } else {
                // Log an error or handle the case where emails are not available
                Log::error("No emails found for reminder ID: " . $reminder->id);
            }
        }

        if ($reminder->reminder_method === 'whatsapp' || $reminder->reminder_method === 'both') {
            if ($reminder->whatsapp_number) {
                $twilioSid = env('TWILIO_SID');
                $twilioToken = env('TWILIO_AUTH_TOKEN');
                $twilioWhatsAppNumber = env('TWILIO_WHATSAPP_NUMBER');
                $recipientNumber = 'whatsapp:+' . $reminder->whatsapp_number;

                $twilio = new Client($twilioSid, $twilioToken);
                try {
                    $message = $twilio->messages
                        ->create($recipientNumber, [
                            "from" => $twilioWhatsAppNumber,
                            'body' => $reminder->title . ': ' . $reminder->description, // Include title and description in message
                        ]);
                } catch (\Exception $e) {

                    Log::error("Error sending WhatsApp reminder: " . $e->getMessage());
                }
            }
        }

        if ($reminder->expires_at && $reminder->expires_at < now()) {
            $reminder->is_reminded = true;
            $reminder->save();
        }
    }
}
