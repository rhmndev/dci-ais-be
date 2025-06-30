<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class StorageSlocAreaRack extends Model
{
    protected $connection = 'mongodb';

    protected $fillable = [
        'storage_sloc_area_code',
        'code',
        'segment',
        'name',
        'position',
        'can_more_item',
        'barcode'
    ];

    public function StorageSlockArea()
    {
        return $this->belongsTo(StorageSlocArea::class,'code','storage_sloc_area_code');
    }
}
