<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Mail\FileUploaded;
use App\Http\Controllers\WhatsAppController;
use Carbon\Carbon;

class File extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'user_npk',
        'original_name',
        'name',
        'disk',
        'path',
        'mime_type',
        'size',
        'file_category',
        'type',
        'extension',
        'created_by',
        'expires_at',
        'is_expired',
        'send_notification',
        'send_notification_only_me',
        'send_notification_to',
        'reminder_datetime',
        'reminder_method',
        'notify_expiry',
        'notification_method',
        'whatsapp_number',
        'remind_at',
        'remind_me_later'
    ];

    protected $casts = [
        'send_notification_to' => 'array',
        'expires_at' => 'datetime',
        'reminder_datetime' => 'datetime',
        'remind_at' => 'datetime',
    ];

    protected $dates = [
        'expires_at',
        'reminder_datetime',
        'remind_at'
    ];

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

    // Get formatted file size
    public function getFormattedSizeAttribute()
    {
        return $this->formatBytes($this->size);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function getIsExpiredAttribute()
    {
        if ($this->expires_at) {
            return $this->expires_at->isPast(); // Use Carbon's isPast() method
        }
        return false; // Not expired if expires_at is null
    }

    public function scopeNearlyExpired($query, $days = 7)
    {
        return $query->where('expires_at', '<=', Carbon::now()->addDays($days))
            ->where('is_expired', false);
    }

    protected static function booted()
    {
        static::saving(function ($file) {
            if ($file->expires_at && $file->expires_at->isPast()) {
                $file->is_expired = true;
            }
        });
    }
}
