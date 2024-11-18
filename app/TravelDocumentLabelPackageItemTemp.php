<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class TravelDocumentLabelPackageItemTemp extends Model
{
    protected $table = 'travel_document_label_package_item_temp';
    protected $fillable = [
        'package_id',
        'package_number',
        'item_number_id',
        'item_number',
    ];

    public function package()
    {
        return $this->belongsTo(TravelDocumentLabelPackageTemp::class, 'package_id');
    }

    public function itemLabel()
    {
        return $this->belongsTo(TravelDocumentLabelTemp::class, 'item_number_id');
    }
}
