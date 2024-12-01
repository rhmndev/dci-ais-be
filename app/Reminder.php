<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

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
        'expires_at',
        'reminder_frequency',
        'frequency_settings',
        'reminder_method',
        'whatsapp_number',
        'emails',
        'files',
        'is_reminded',
    ];

    protected $casts = [
        // 'reminder_datetime' => 'datetime',
        // 'expires_at' => 'datetime',
        'frequency_settings' => 'array',
        'emails' => 'array',
        'files' => 'array',
        'is_reminded' => 'boolean',
    ];

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
