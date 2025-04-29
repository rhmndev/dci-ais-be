<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class OutgoingGoodTemplate extends Model
{
    protected $fillable = [
        'code_template', // Unique identifier for the outgoing good template
        'name_template', // Name of the template
        'priority', // Priority of the outgoing good
        'date_out', // Date when the part was sent out
        'outgoing_location', // Location where the part is sent out
        'handle_for', // Person or entity handling the outgoing good
        'handle_for_type', // Type of person or entity handling the outgoing good (e.g., 'internal', 'external')
        'handle_for_id', // ID of the person or entity handling the outgoing good
        'take_material_from_location', // Location from where the material is taken
        'component_code', // Code of the component being sent out
        'component_name', // Name of the component being sent out
        'assigned_to', // Person or entity assigned to handle the outgoing good
        'handle_by', // Person or entity who handled the outgoing good
        'rel_state', // State of the outgoing good (e.g., 'pending', 'completed'),
        'status', // Status of the outgoing good (e.g., 'active', 'inactive')
        'created_by', // User who created the outgoing good record
        'updated_by', // User who last updated the outgoing good record
        'notes', // Additional notes or comments
    ];

    public function items()
    {
        return $this->hasMany(OutgoingGoodTemplateItem::class, 'code_template', 'code_template');
    }
}
