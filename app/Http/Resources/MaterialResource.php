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
        $this->materialType;
        return [
            '_id' => $this->_id, // Assuming _id is your primary key in the supplier table
            'code' => $this->code,
            'description' => $this->description,
            'photo' => $this->photo,
            'type' => $this->type,
            'unit' => $this->unit,
            'default_packing_qty' => $this->whenLoaded('materialType') ? $this->materialType->pack_qty : 1,
        ];
    }
}
