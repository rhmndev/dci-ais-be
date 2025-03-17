<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['code'];

    public static function getCustomerList($customerName = null)
    {
        $customers = [
            ['code_name' => 'P1P1', 'customer' => 'AHM'],
            ['code_name' => 'P1P2', 'customer' => 'AHM'],
            ['code_name' => 'P2P1', 'customer' => 'AHM'],
            ['code_name' => 'P2P2', 'customer' => 'AHM'],
            ['code_name' => 'P3P4', 'customer' => 'AHM'],
            ['code_name' => 'P3P1', 'customer' => 'AHM'],
            ['code_name' => 'CBA 1', 'customer' => 'AHM'],
            ['code_name' => 'P7P1', 'customer' => 'AHM'],
            ['code_name' => 'P8P1', 'customer' => 'AHM'],
            ['code_name' => 'P9P1', 'customer' => 'AHM'],
            ['code_name' => 'CHEMCO CIKARANG', 'customer' => 'CHEMCO'],
            ['code_name' => 'TEC CIBITUNG', 'customer' => 'TEC'],
            ['code_name' => 'TD LINK', 'customer' => 'TOYOTA'],
            ['code_name' => 'PLANT 2 KARAWANG', 'customer' => 'TOYOTA'],
            ['code_name' => 'EXPORT', 'customer' => 'TOYOTA'],
            ['code_name' => 'SAP ASSY 1', 'customer' => 'DAIHATSU'],
            ['code_name' => 'KAP ASSY 3', 'customer' => 'DAIHATSU'],
            ['code_name' => 'SUZUKI TAMBUN', 'customer' => 'SUZUKI'],
            ['code_name' => 'HYUNDAI CIKARANG', 'customer' => 'HYUNDAI'],
        ];

        if ($customerName) {
            return array_filter($customers, function ($customer) use ($customerName) {
                return $customer['customer'] == $customerName;
            });
        }

        return $customers;
    }

    public function getAllData($keyword, $columns, $sort, $order)
    {

        $query = Customer::query();

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

        $query = Customer::query();
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

    public function partcomponents()
    {
        return $this->hasMany(PartComponent::class);
    }
}
