<?php

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::truncate();
        $permissions = Permission::get();
        $datas = [
            [
                'name' => 'Admin',
                'description' => 'Administrator',
                'has_head_of_department' => false,
                'head_of_department_id' => null,
                'permissions' => $permissions->map(function ($perm) {
                    return [
                        'permission_id' => $perm->id,
                        'slug' => $perm->slug,
                        'allow' => true
                    ];
                })->toArray(),
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'name' => 'Vendor',
                'description' => 'Vendor',
                'has_head_of_department' => false,
                'head_of_department_id' => null,
                'permissions' => $permissions->map(function ($perm) {
                    if ($perm->url == 'dashboard' || $perm->url == 'transaction' || $perm->url == 'receiving-vendor') {

                        return [
                            'permission_id' => $perm->id,
                            'slug' => $perm->slug,
                            'allow' => true
                        ];
                    }
                })->toArray(),
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'name' => 'Supplier',
                'description' => 'Supplier',
                'has_head_of_department' => false,
                'head_of_department_id' => null,
                'permissions' => $permissions->map(function ($perm) {
                    if ($perm->url == 'dashboard' || $perm->url == 'supplier-area') {

                        return [
                            'permission_id' => $perm->id,
                            'slug' => $perm->slug,
                            'allow' => true
                        ];
                    }
                })->toArray(),
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'name' => 'Purchasing',
                'description' => 'Purchasing',
                'has_head_of_department' => false,
                'head_of_department_id' => null,
                'permissions' => $permissions->map(function ($perm) {
                    if ($perm->url == 'dashboard' || $perm->url == 'purchase-order' || $perm->url == 'MonitoringPO' || $perm->url == 'delivery-schedule') {

                        return [
                            'permission_id' => $perm->id,
                            'slug' => $perm->slug,
                            'allow' => true
                        ];
                    }
                })->toArray(),
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'name' => 'Warehouse',
                'description' => 'Warehouse',
                'has_head_of_department' => false,
                'head_of_department_id' => null,
                'permissions' => $permissions->map(function ($perm) {
                    if ($perm->url == 'dashboard' || $perm->url == 'warehouse-area' || $perm->url == 'delivery-schedule' || $perm->url == 'receiving-checkpoint') {

                        return [
                            'permission_id' => $perm->id,
                            'slug' => $perm->slug,
                            'allow' => true
                        ];
                    }
                })->toArray(),
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'name' => 'Director',
                'description' => 'Director',
                'has_head_of_department' => false,
                'head_of_department_id' => null,
                'permissions' => $permissions->map(function ($perm) {
                    if ($perm->url == 'dashboard') {

                        return [
                            'permission_id' => $perm->id,
                            'slug' => $perm->slug,
                            'allow' => true
                        ];
                    }
                })->toArray(),
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
        ];

        foreach ($datas as $data) {

            $role = Role::firstOrNew(['name' => $data['name']]);
            $role->name = $data['name'];
            $role->description = $data['description'];
            $role->permissions = $data['permissions'];
            $role->created_by = $data['created_by'];
            $role->updated_by = $data['updated_by'];
            $role->save();
        }
    }
}
