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
        'status',           // Status of material scanning (e.g., 'pending', 'scanned')
        'scans',            // Array of scan records
    ];

    protected $casts = [
        'scans' => 'array',
    ];

    public function outgoingGood()
    {
        return $this->belongsTo(OutgoingGood::class, 'outgoing_good_number', 'number');
    }

    /**
     * Add a new scan record to the item
     *
     * @param string $qrCode
     * @param string $userId
     * @param float $quantity
     * @param array $additionalInfo
     * @return void
     */
    public function addScan($qrCode, $userId, $quantity, $additionalInfo = [])
    {
        $scans = $this->scans ?? [];
        $scans[] = [
            'qr_code' => $qrCode,
            'scanned_at' => now(),
            'scanned_by' => $userId,
            'quantity' => $quantity,
            'reference' => $additionalInfo
        ];
        
        $this->scans = $scans;
        $this->quantity_out = $this->calculateTotalScannedQuantity();
        $this->status = $this->quantity_out >= $this->quantity_needed ? 'scanned' : 'partially_scanned';
        $this->save();
    }

    /**
     * Get all scans for this item
     *
     * @return array
     */
    public function getScans()
    {
        return $this->scans ?? [];
    }

    /**
     * Calculate total quantity scanned across all scans
     *
     * @return float
     */
    public function calculateTotalScannedQuantity()
    {
        $scans = $this->getScans();
        return array_sum(array_column($scans, 'quantity'));
    }

    /**
     * Check if the item has been fully scanned
     *
     * @return bool
     */
    public function isFullyScanned()
    {
        return $this->quantity_out >= $this->quantity_needed;
    }
}
