<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class OutgoingGoodTemplateItem extends Model
{
    protected $fillable = [
        'code_template', // Reference to the OutgoingGoodTemplate model
        'material_code',     // Code of the part being sent out
        'material_name',     // Name of the part being sent out
        'quantity_needed',     // Quantity of the part needed
        'uom_needed',        // Unit of Measure for the quantity needed
        'created_by',       // User who created the outgoing good item record
        'updated_by',       // User who last updated the outgoing good item
    ];

    public function outgoingGoodTemplate()
    {
        return $this->belongsTo(OutgoingGoodTemplate::class, 'code_template', 'code_template');
    }
}
