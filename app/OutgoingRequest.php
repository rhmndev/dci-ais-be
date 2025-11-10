<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OutgoingRequest extends Model
{
    use SoftDeletes;

    protected $table = 'outgoing_requests';

    protected $fillable = [
        'outgoing_no',
        'po_number',
        'supplier_name',
        'supplier_code',
        'customer_name',
        'delivery_date',
        'driver_name',
        'items',
        'current_status',
        'qr_code',
        'is_archived',
        'archived_at',
        'archived_by',
        'archived_reason',
        'good_receipt_ref',
        'good_receipt_reference' // Alias untuk konsistensi
    ];

    protected $casts = [
        'items' => 'array',
        'delivery_date' => 'date',
        'archived_at' => 'datetime',
        'is_archived' => 'boolean'
    ];

    protected $dates = [
        'delivery_date',
        'archived_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relationships
    public function goodReceipts()
    {
        return $this->hasMany(GoodReceipt::class, 'outgoing_number', 'outgoing_no');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function scopePending($query)
    {
        return $query->where('current_status', 'Pending');
    }

    // Mutators
    public function setQrCodeAttribute($value)
    {
        // Auto-generate QR code if not provided
        if (empty($value) && !empty($this->outgoing_no)) {
            $this->attributes['qr_code'] = $this->outgoing_no;
        } else {
            $this->attributes['qr_code'] = $value;
        }
    }

    // Accessors
    public function getStatusColorAttribute()
    {
        switch ($this->current_status) {
            case 'Pending':
                return 'warning';
            case 'Completed':
                return 'success';
            case 'Cancelled':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    public function getItemsCountAttribute()
    {
        return is_array($this->items) ? count($this->items) : 0;
    }

    // Methods
    public function archive($archivedBy = 'System', $reason = 'Good Receipt processed', $goodReceiptRef = null)
    {
        $this->update([
            'is_archived' => true,
            'archived_at' => now(),
            'archived_by' => $archivedBy,
            'archived_reason' => $reason,
            'good_receipt_ref' => $goodReceiptRef
        ]);
    }

    public function unarchive()
    {
        $this->update([
            'is_archived' => false,
            'archived_at' => null,
            'archived_by' => null,
            'archived_reason' => null,
            'good_receipt_ref' => null
        ]);
    }

    public function canBeArchived()
    {
        return !$this->is_archived && $this->current_status !== 'Cancelled';
    }

    public function generateQrCode()
    {
        if (empty($this->qr_code)) {
            $this->qr_code = $this->outgoing_no;
            $this->save();
        }
        return $this->qr_code;
    }
}
