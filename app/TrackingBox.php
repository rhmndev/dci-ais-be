<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class TrackingBox extends Model
{
    protected $fillable = [
        'number_box',
        'dn_number',
        'kanban',
        'customer',
        'customer_alias',
        'plant',
        'destination_code',
        'destination_aliases',
        'status',
        'date_time',
        'scanned_by',
    ];

    public function Box()
    {
        return $this->belongsTo(Box::class, 'number_box', 'number_box');
    }

    public function getTrackingBoxes()
    {
        return TrackingBox::where('dn_number', $this->dn_number)->get();
    }

    public function getKanbanLabelInternal()
    {
        if ($this->kanban == null) {
            return null;
        }

        $type = '';

        $compare = CompareDeliveryNote::where('kanban', $this->kanban)->first();

        if ($compare) {
            $type = "ADM";
        }

        $compareAhm = CompareDeliveryNoteAHM::where('job_seq', $this->kanban)->first();

        if ($compareAhm) {
            $type = "AHM";
        }

        switch ($type) {
            case 'ADM':
                return $this->getKanbanLabelADM($this->kanban);
                break;
            case 'AHM':
                return $this->kanban . ' (AHM)';
                break;
            default:
                return $this->kanban;
                break;
        }

        return $this->kanban;
    }

    private function getKanbanLabelADM($kanban)
    {
        $compare = CompareDeliveryNote::where('kanban', $kanban)->first();

        $data = [];
        if ($compare) {
            $jobNo = $compare->job_no;

            $labelDetail = LabelInternalDetailAdm::with('labelInternalProduksi')->where('job_no', $jobNo)->first();

            if ($labelDetail->labelInternalProduksi) {
                return $labelDetail->labelInternalProduksi;
            }
        }

        return $data;
    }
}
