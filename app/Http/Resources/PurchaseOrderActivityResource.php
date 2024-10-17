<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            '_id' => $this->_id,
            'po_number' => $this->po_number,
            'seen' => $this->seen,
            'last_seen_at' => $this->last_seen_at,
            'downloaded' => $this->downloaded,
            'last_downloaded_at' => $this->last_downloaded_at,
        ];
    }
}
