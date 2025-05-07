<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class OutgoingGood extends Model
{
    protected $fillable = [
        'number', // Unique identifier for the outgoing good
        'date', // Date of the outgoing good
        'time', // Time of the outgoing good
        'priority', // Priority of the outgoing good
        'part_code', // Code of the part being sent out
        'part_number', // Number of the part being sent out
        'part_name', // Name of the part being sent out
        'date_out', // Date when the part was sent out
        'component_code', // Code of the component being sent out
        'component_name', // Name of the component being sent out
        'outgoing_location', // Location where the part is sent out
        'handle_for', // Person or entity handling the outgoing good
        'handle_for_type', // Type of person or entity handling the outgoing good (e.g., 'internal', 'external')
        'handle_for_id', // ID of the person or entity handling the outgoing good
        'take_material_from_location', // Location from where the material is taken
        'assigned_to', // Person or entity assigned to handle the outgoing good
        'handle_by', // Person or entity who handled the outgoing good
        'rel_state', // State of the outgoing good (e.g., 'pending', 'completed'),
        'qr_code', // QR code for the outgoing good,
        'status', // Status of the outgoing good (e.g., 'active', 'inactive')
        'created_by', // User who created the outgoing good re
        'updated_by', // User who last updated the outgoing good
        'notes', // Additional notes or comments
        'is_completed', // Flag to indicate if the outgoing good has been completed
        'completed_at', // Date and time when the outgoing good was completed
        'completed_by', // User who completed the outgoing good
        'received_by', // Person who received the outgoing good
        'received_date', // Date when the outgoing good was received
        'handed_over_by', // Person who handed over the outgoing good
        'handed_over_date', // Date when the outgoing good was handed over
        'acknowledged_by', // Person who acknowledged the outgoing good
        'acknowledged_date', // Date when the outgoing good was acknowledged
        'requested_by', // Person who requested the outgoing good
        'requested_date', // Date when the outgoing good was requested
    ];

    public function items()
    {
        return $this->hasMany(OutgoingGoodItem::class, 'outgoing_good_number', 'number');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to', '_id');
    }

    /**
     * Check if all items in the outgoing good have been scanned
     *
     * @return bool
     */
    public function allItemsScanned()
    {
        return $this->items()->where('status', '!=', 'scanned')->count() === 0;
    }
}
