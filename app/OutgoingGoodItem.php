<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class OutgoingGoodItem extends Model
{
    protected $fillable = [
        'outgoing_good_number', // Reference to the OutgoingGood model
        'material_code',     // Code of the part being sent out
        'material_name',     // Name of the part being sent out
        'alias',
        'part_number',
        'quantity_needed',     // Quantity of the part needed
        'quantity_out',        // Quantity of the part sent out
        'uom_needed',        // Unit of Measure for the quantity needed
        'uom_out',          // Unit of Measure for the quantity sent out
        'created_by',       // User who created the outgoing good item record
        'updated_by',       // User who last updated the outgoing good item
        'status',           // Status of material scanning (e.g., 'pending', 'scanned')
        'list_need_scans',  // List of scans needed for this item
        'scans',            // Array of scan records
    ];

    protected $casts = [
        'scans' => 'array',
        'list_need_scans' => 'array',
    ];

    public function outgoingGood()
    {
        return $this->belongsTo(OutgoingGood::class, 'outgoing_good_number', 'number');
    }

    public function getListNeedScans()
    {
        $scans = $this->list_need_scans ?? [];
        return $scans;
    }

    public function addListNeedScans($jobSeq, $rack, $quantity, $uom)
    {
        $scans = $this->list_need_scans ?? [];
        
        // Check if this job_seq and rack_code combination already exists
        $exists = collect($scans)->contains(function($scan) use ($jobSeq, $rack) {
            return $scan['job_seq'] === $jobSeq && $scan['rack_code'] === $rack;
        });

        if (!$exists) {
            $scans[] = [
                'job_seq' => $jobSeq,
                'rack_code' => $rack,
                'quantity' => $quantity,
                'uom' => $uom
            ];
            $this->list_need_scans = $scans;
            $this->save();

            // Log the activity in the parent OutgoingGood
            $this->outgoingGood->logActivity('scan_list_updated', [
                'item_id' => $this->_id,
                'material_code' => $this->material_code,
                'added_scan' => [
                    'job_seq' => $jobSeq,
                    'rack_code' => $rack,
                    'quantity' => $quantity,
                    'uom' => $uom
                ]
            ], 'Added new scan requirement');
        }
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
    public function addScan($qrCode, $userId, $quantity, $uom, $additionalInfo = [])
    {
        $scans = $this->scans ?? [];
        $newScan = [
            'qr_code' => $qrCode,
            'scanned_at' => now(),
            'scanned_by' => $userId,
            'quantity' => $quantity,
            'uom' => $uom,
            'reference' => $additionalInfo
        ];
        
        $scans[] = $newScan;
        $this->scans = $scans;
        $this->quantity_out = $this->calculateTotalScannedQuantity();
        $oldStatus = $this->status;
        $this->status = $this->quantity_out >= $this->quantity_needed ? 'scanned' : 'partially_scanned';
        $this->save();

        // Log the activity in the parent OutgoingGood
        $this->outgoingGood->logActivity('item_scanned', [
            'item_id' => $this->_id,
            'material_code' => $this->material_code,
            'scan_details' => $newScan,
            'old_status' => $oldStatus,
            'new_status' => $this->status,
            'quantity_out' => $this->quantity_out,
            'quantity_needed' => $this->quantity_needed
        ]);

        // If all items are scanned, this will trigger the logging in the parent
        $this->outgoingGood->allItemsScanned();
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
