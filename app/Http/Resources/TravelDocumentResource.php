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
            'order_delivery_date' => $this->order_delivery_date,
            'supplier_code' => $this->supplier_code,
            'shipping_address' => $this->shipping_address,
            'driver_name' => $this->driver_name,
            'vehicle_number' => $this->vehicle_number,
            'notes' => $this->notes,
            'status' => $this->status,
            'made_by_user' => $this->made_by_user,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_scanned' => $this->is_scanned,
            'scanned_by' => $this->scanned_by,
            'scanned_by_user' => $this->whenLoaded('scannedUserBy'),
            'scanned_at' => $this->scanned_at,
            'purchase_order' => new PurchaseOrderResource($this->whenLoaded('purchaseOrder')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'items' => $this->whenLoaded('items', function () {
                return collect($this->items)->groupBy('po_item_id')->map(function ($group, $poItemId) {
                    $firstItem = $group->first();
                    return [
                        'po_item_id' => $poItemId,
                        'material' => new MaterialResource($firstItem->poItem->material),
                        'poItem' => new PurchaseOrderItemsResource($firstItem->poItem),
                        'total_qty' => $group->sum('qty'),
                        'items' => $group->map(function ($item) {
                            return [
                                'id' => $item->_id,
                                'qty' => $item->qty,
                                'qr_uuid' => $item->qr_uuid,
                                'qr_path' => $item->qr_path,
                                'qr_tdi_no' => $item->qr_tdi_no,
                                'lot_production_number' => $item->lot_production_number,
                                'inspector_name' => $item->tempLabelItem->inspector_name ?: "-",
                                'inspector_date' => $item->tempLabelItem->inspection_date ?: "-",
                                'is_scanned' => $item->is_scanned,
                                'scanned_at' => $item->scanned_at,
                                'scanned_by' => $item->scanned_by,
                            ];
                        }),
                    ];
                });
            }),
        ];
    }
}
