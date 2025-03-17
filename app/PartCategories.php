<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartCategories extends Model
{
    protected $fillable = [
        'code',
        'name',
        'created_by',
        'updated_by',
    ];

    public function parts()
    {
        return $this->hasMany(Part::class, 'category_code', 'code');
    }
}
