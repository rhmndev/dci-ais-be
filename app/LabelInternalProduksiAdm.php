<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabelInternalProduksiAdm extends Model
{
    protected $connection = 'mysql';

    protected $table = 'label_internal_produksi';

    protected $fillable = [
        'id',
        'part_no',
        'part_name',
        'job_no',
        'qty',
        'last_upd',
        'user_id',
    ];

    public function labelInternalDetailAdm()
    {
        return $this->hasMany(LabelInternalDetailAdm::class, 'id_label', 'id');
    }
}
