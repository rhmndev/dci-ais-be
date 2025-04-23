<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class WhsMaterialControlLog extends Model
{
    protected $fillable = [
        'whs_material_control_id', // Reference to the WhsMaterialControl model
        'action',                  // Action performed (e.g., 'created', 'updated', 'deleted', 'stock_out')
        'changes',                 // JSON field to store changes made
        'performed_by',            // User who performed the action
        'performed_at',            // Timestamp of the action
    ];

    public function whsMaterialControl()
    {
        return $this->belongsTo(WhsMaterialControl::class, 'whs_material_control_id');
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by', 'npk');
    }
}
