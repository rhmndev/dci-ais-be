<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class OutgoingGoodLog extends Model
{
    protected $fillable = [
        'outgoing_good_number',  // Reference to the OutgoingGood number
        'action',                // Action performed (e.g., 'created', 'updated', 'completed', 'scanned')
        'changes',               // JSON field to store changes made
        'performed_by',          // User who performed the action
        'performed_at',          // Timestamp of when the action occurred
        'notes',                 // Additional notes about the action
    ];

    public function outgoingGood()
    {
        return $this->belongsTo(OutgoingGood::class, 'outgoing_good_number', 'number');
    }

    public function performedByUser()
    {
        return $this->belongsTo(User::class, 'performed_by', '_id');
    }
} 