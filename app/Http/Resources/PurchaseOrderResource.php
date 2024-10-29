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
        $this->slock;
        $this->items;
        $this->supplier;
        $this->checkedUserBy;
        $this->knowedUserBy;
        $this->approvedUserBy;

        return [
            '_id' => $this->_id,
            'po_number' => $this->po_number,
            'order_date' => $this->order_date,
            'delivery_date' => $this->delivery_date,
            'delivery_address' => $this->delivery_address,
            'total_item_quantity' => $this->total_item_quantity,
            'tax' => $this->tax,
            'subtotal' => $this->subtotal,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'purchase_currency_type' => $this->purchase_currency_type,
            'purchase_type' => $this->purchase_type,
            'purchase_checked_by' => $this->purchase_checked_by,
            'checked_at' => $this->checked_at,
            'is_checked' => $this->is_checked,
            'purchase_knowed_by' => $this->purchase_knowed_by,
            'knowed_at' => $this->knowed_at,
            'is_knowed' => $this->is_knowed,
            'purchase_agreement_by' => $this->purchase_agreement_by,
            'approved_at' => $this->approved_at,
            'is_approved' => $this->is_approved,
            'user_checked' => new PurchaseOrderUserResource($this->whenLoaded('checkedUserBy')),
            'user_knowed' => new PurchaseOrderUserResource($this->whenLoaded('knowedUserBy')),
            'user_approved' => new PurchaseOrderUserResource($this->whenLoaded('approvedUserBy')),
            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    $item->material = isset($item->material) ? $item->material : '';
                    return new PurchaseOrderItemsResource($item);
                });
            }),
            's_lock' => new SLockResource($this->whenLoaded('s_lock')),
            'supplier' => new SupplierResource($this->whenLoaded('supplier')),
            'is_send_email_to_supplier' => $this->is_send_email_to_supplier,
            'qr_uuid' => $this->qr_uuid,
            'qr_data' => '',
            'notes' => $this->notes,
            'notes_from_checker' => $this->notes_from_checker,
            'notes_from_knower' => $this->notes_from_knower,
            'notes_from_approver' => $this->notes_from_approver,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
        ];
    }
}
