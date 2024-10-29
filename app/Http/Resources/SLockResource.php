<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SLockResource extends JsonResource
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
            'code' => $this->code,
            'description' => $this->description,
        ];
    }
}
