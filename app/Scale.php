<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Scale extends Model
{
    //
    public function getData($type)
    {
        $query = Scale::query();
        if($type == 1){
            $query = $query->first();
        } else {
            $query = $query->get();
        }

        return $query;
    }
}
