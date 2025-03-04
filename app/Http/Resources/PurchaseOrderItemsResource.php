<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $this->travelDocumentItem;

        return [
            '_id' => $this->_id,
            'material' => new MaterialResource($this->whenLoaded('material')),
            'quantity' => $this->quantity,
            'unit_type' => $this->unit_type,
            'unit_price' => $this->unit_price,
            'unit_price_type' => $this->unit_price_type,
            'total_price' => $this->unit_price * $this->quantity,
            'travel_document_items_qty_delivered' => $this->whenLoaded('travelDocumentItem', function () {
                return $this->travelDocumentItem->sum('qty');
            }),
            'qty_remaining' => $this->quantity - ($this->qty_delivered ?? 0),
            'travel_document_items_count' => $this->whenLoaded('travelDocumentItem', function () {
                return $this->travelDocumentItem->count();
            }),
            'qty_on_delivery' => $this->whenLoaded('travelDocumentItem', function () {
                return $this->travelDocumentItem
                    ->where('is_scanned', '!=', true)
                    ->sum('qty');
            }),
            'qty_delivered' => $this->qty_delivered ?? 0,
        ];
    }
}
