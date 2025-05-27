<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Jenssegers\Mongodb\Eloquent\SoftDeletes;

class Material extends Model
{
    use SoftDeletes;

    protected $fillable = ['code','is_partially_out'];
    // adding for can multi standar

    public function getAllData($keyword, $columns, $sort, $order, $category = null)
    {

        $query = Material::query();

        if (!empty($keyword)) {

            foreach ($columns as $index => $column) {

                if ($index == 0) {

                    $query = $query->where($column, 'like', '%' . $keyword . '%');
                } else {

                    $query = $query->orWhere($column, 'like', '%' . $keyword . '%');
                }
            }
        }

        if ($category) {
            $query->where('category', $category);
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->get();

        return $data;
    }

    public function getData($keyword, $columns, $perpage, $page, $sort, $order, $category = null)
    {

        $query = Material::query();
        $skip = $perpage * ($page - 1);

        if (!empty($keyword)) {

            foreach ($columns as $index => $column) {

                if ($index == 0) {

                    $query = $query->where($column, 'like', '%' . $keyword . '%');
                } else {

                    $query = $query->orWhere($column, 'like', '%' . $keyword . '%');
                }
            }
        }

        if ($category) {
            $query->where('category', $category);
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->take((int)$perpage)->skip((int)$skip)->get();

        return $data;
    }

    public function checkMaterial($code)
    {
        $query = Material::query();

        $query = $query->where('code', $code)->get();

        return $query;
    }

    public function materialType()
    {
        return $this->hasOne(MaterialType::class, 'name', 'type');
    }

    public function roleMaterialTypes()
    {
        return $this->hasMany(RoleMaterialType::class, 'material_type', 'type');
    }

    public function StockSlockData()
    {
        return $this->hasMany(StockSlock::class, 'material_code', 'code');
    }
}
