<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabelInternalDetailAdm extends Model
{
    protected $connection = 'mysql';

    protected $table = 'label_internal_detail';

    protected $fillable = [
        'id_label',
        'job_no',
        'seq',
        'is_print',
        'count',
        'inspektor',
        'tgl_inspeksi',
        'last_upd',
        'user_id'
    ];

    public function labelInternalProduksi()
    {
        return $this->belongsTo(LabelInternalProduksiAdm::class, 'id_label', 'id');
    }
}
