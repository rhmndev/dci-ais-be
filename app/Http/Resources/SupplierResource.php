<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            // ... other supplier attributes you want to expose
        ];
    }
}
