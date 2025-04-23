<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class OutgoingGood extends Model
{
    protected $fillable = [
        'number', // Unique identifier for the outgoing good
        'date', // Date of the outgoing good
        'part_code', // Code of the part being sent out
        'part_name', // Name of the part being sent out
        'date_out', // Date when the part was sent out
        'outgoing_location', // Location where the part is sent out
        'rel_state', // State of the outgoing good (e.g., 'pending', 'completed'),
        'qr_code', // QR code for the outgoing good,
        'created_by', // User who created the outgoing good re
        'updated_by', // User who last updated the outgoing good
    ];
}
