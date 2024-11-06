<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
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
            '_id' => $this->_id, // Assuming _id is your primary key in the supplier table
            'code' => $this->code,
            'description' => $this->description,
            'photo' => $this->photo,
            'type' => $this->type,
            'packing_qty' => $this->whenLoaded('TypePackingQty'),
            'default_total_print_label' => $this->default_total_print_label ?? 1,
            'default_packing_qty' => $this->default_packing_qty ?? 0,
        ];
    }
}
