<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
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
            '_id' => $this->id,
            'po_number' => $this->po_number,
            'order_date' => $this->order_date,
            'delivery_date' => $this->delivery_date,
            'delivery_address' => $this->delivery_address,
            'total_item_quantity' => $this->total_item_quantity,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'purchase_currency_type' => $this->purchase_currency_type,
            'purchase_type' => $this->purchase_type,
            'purchase_checked_by' => $this->purchase_checked_by,
            'checked_at' => $this->checked_at,
            'purchase_knowed_by' => $this->purchase_knowed_by,
            'knowed_at' => $this->knowed_at,
            'purchase_agreement_by' => $this->purchase_agreement_by,
            'approved_at' => $this->approved_at,
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return new PurchaseOrderItemsResource($item);
                });
            }),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ];
    }
}
