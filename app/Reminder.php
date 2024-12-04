<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;
use DateTimeInterface;

class Reminder extends Model
{
    protected $fillable = [
        'user_id',
        'username',
        'remindable_type',
        'remindable_id',
        'title',
        'description',
        'reminder_datetime',
        'starred',
        'category',
        'expires_at',
        'is_repeat',
        'repeat_freq',
        'repeat_interval',
        'repeat_reminder_time',
        'repeat_start_date',
        'repeat_day_of_month',
        'repeat_day_of_week',
        'repeat_end_type',
        'repeat_end_date',
        'repeat_end_occurrences',
        'reminder_frequency',
        'reminder_interval_day',
        'reminder_interval_week',
        'reminder_interval_month',
        'reminder_interval_year',
        'last_reminder_at',
        'reminder_method',
        'whatsapp_number',
        'emails',
        'files',
        'is_reminded',
        'ends_at',
        'max_occurrences',
        'notify_until_expired',
        'notify_interval',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'repeat_start_date' => 'datetime',
        'last_reminder_at' => 'datetime',
        'frequency_settings' => 'array',
        'emails' => 'array',
        'files' => 'array',
        'is_reminded' => 'boolean',
        'starred' => 'boolean',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i');  // Format to 'YYYY-MM-DD HH:mm'
    }

    /**
     * Get the remindable model.
     */
    public function remindable()
    {
        return $this->morphTo();
    }

    // Example relationship to the User model (adjust as needed)
    public function user()
    {
        return $this->belongsTo(User::class); // Assuming you have a User model
    }
}
