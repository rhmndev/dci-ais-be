<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class PartComponent extends Model
{
    protected $fillable = ['customer_id', 'name', 'number'];

    public function getAllData($keyword, $columns, $sort, $order)
    {

        $query = PartComponent::query();

        if (!empty($keyword)) {

            foreach ($columns as $index => $column) {

                if ($index == 0) {

                    $query = $query->where($column, 'like', '%' . $keyword . '%');
                } else {

                    $query = $query->orWhere($column, 'like', '%' . $keyword . '%');
                }
            }
        }

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->get();

        return $data;
    }

    public function getData($keyword, $columns, $perpage, $page, $sort, $order)
    {

        $query = PartComponent::query();
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

        $query = $query->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc');

        $data = $query->take((int)$perpage)->skip((int)$skip)->get();

        return $data;
    }

    public function getDataGroupedByCustomer($keyword, $columns, $perpage, $page, $sort, $order)
    {
        $query = PartComponent::query();

        if (!empty($keyword)) {
            $query->where(function ($q) use ($columns, $keyword) {
                foreach ($columns as $index => $column) {
                    if ($index == 0) {
                        $q->where($column, 'like', '%' . $keyword . '%');
                    } else {
                        $q->orWhere($column, 'like', '%' . $keyword . '%');
                    }
                }
            });
        }

        $skip = $perpage * ($page - 1);

        $data = $query->groupBy('customer_id')
            ->orderBy($sort, $order == 'ascend' ? 'asc' : 'desc')
            ->take((int)$perpage)
            ->skip((int)$skip)
            ->get();

        // Transform the data to have a more useful structure
        $groupedData = [];
        foreach ($data as $partComponent) {
            $customerId = $partComponent->customer_id;
            if (! isset($groupedData[$customerId])) {
                $groupedData[$customerId] = [
                    'customer_id' => $customerId,
                    'parts' => []
                ];
            }
            $groupedData[$customerId]['parts'][] = $this->getAllData($keyword, $columns, $sort, $order)->where('customer_id', $customerId);
        }

        return $groupedData;
    }

    public function customer()
    {
        return $this->belongsTo('App\Customer');
    }
}
