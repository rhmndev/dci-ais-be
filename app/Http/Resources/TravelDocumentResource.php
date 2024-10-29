<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TravelDocumentResource extends JsonResource
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
            'no' => $this->no,
            'po_number' => $this->po_number,
            'po_date' => $this->po_date,
            'supplier_code' => $this->supplier_code,
            'shipping_address' => $this->shipping_address,
            'driver_name' => $this->driver_name,
            'vehicle_number' => $this->vehicle_number,
            'notes' => $this->notes,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->_id,
                        'po_item_id' => $item->po_item_id,
                        'material' => new MaterialResource($item->poItem->material),
                        'poItem' => new PurchaseOrderItemsResource($item->poItem),
                    ];
                });
            }),
        ];
    }
}
