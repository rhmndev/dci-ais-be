<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Reminder;

class ReminderNotification extends Notification
{
    use Queueable;

    protected $reminder;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $reminder = $this->reminder;

        $mailMessage = (new MailMessage)
            ->subject("[Reminder] " . $reminder->title . " - " . config('app.name'))
            ->line($reminder->description);

        if ($reminder->expires_at) {
            $mailMessage->line('Expires at: ' . $reminder->expires_at);
        }


        // Add any relevant files if they are part of the reminder.
        if ($reminder->files && is_array($reminder->files)) {
            // Assuming 'files' contains URLs or paths to files you want to attach
            foreach ($reminder->files as $file) {
                $mailMessage->attach(storage_path('app/public/' . $file));
            }
        }

        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'title' => $this->reminder->title,
            'description' => $this->reminder->description,
            'reminder_datetime' => $this->reminder->reminder_datetime,
        ];
    }
}
