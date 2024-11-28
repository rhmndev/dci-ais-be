<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\FileUploaded;
use App\Http\Controllers\WhatsAppController;

class File extends Model
{
    protected $fillable = ['user_id', 'name', 'path', 'size', 'type', 'ext', 'expires_at', 'is_expired'];

    protected $dates = ['expires_at'];

    const MAX_UPLOAD_SIZE = 5120;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sendNotifications()
    {
        if (!$this->send_notification) {
            return; // Notifications disabled for this file
        }

        $uploadedBy = User::find($this->created_by); // Get user who uploaded
        $fileName = $this->name;

        // 1. Email Notification
        $this->sendEmailNotification($uploadedBy, $fileName);

        // 2. WhatsApp Notification (Example - adapt as needed)
        $this->sendWhatsAppNotification($uploadedBy, $fileName);
    }

    private function sendEmailNotification($uploadedBy, $fileName)
    {
        $recipients = [];
        if ($this->send_notification_only_me) {
            // Send only to the uploader
            $recipients[] = $uploadedBy->email;
        } else {
            // Send to specified users or get all users (implement your logic)
            $recipients = explode(',', $this->send_notification_to);
        }

        Mail::to($recipients)->send(new FileUploaded($uploadedBy, $fileName));
    }

    private function sendWhatsAppNotification($uploadedBy, $fileName)
    {
        // **IMPORTANT:** Replace with your actual WhatsApp sending logic
        $message = "New file uploaded: $fileName by {$uploadedBy->name}";
        $recipientNumber = '+1234567890'; // Replace with recipient number
        WhatsAppController::sendWhatsAppMessage($recipientNumber, $message);
    }
}
