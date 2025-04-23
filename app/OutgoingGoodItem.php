<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class OutgoingGoodItem extends Model
{
    protected $fillable = [
        'outgoing_good_number', // Reference to the OutgoingGood model
        'material_code',     // Code of the part being sent out
        'material_name',     // Name of the part being sent out
        'quantity_needed',     // Quantity of the part needed
        'quantity_out',        // Quantity of the part sent out
        'uom_needed',        // Unit of Measure for the quantity needed
        'uom_out',          // Unit of Measure for the quantity sent out
        'created_by',       // User who created the outgoing good item record
        'updated_by',       // User who last updated the outgoing good item
    ];

    public function outgoingGood()
    {
        return $this->belongsTo(OutgoingGood::class, 'outgoing_good_number', 'number');
    }
}
