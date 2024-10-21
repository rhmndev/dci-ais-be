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
                'permissions' => $permissions->map(function ($perm) {
                    return [
                        'permission_id' => $perm->id,
                        'allow' => true
                    ];
                })->toArray(),
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'name' => 'Vendor',
                'description' => 'Vendor',
                'permissions' => $permissions->map(function ($perm) {
                    if ($perm->url == 'dashboard' || $perm->url == 'transaction' || $perm->url == 'receiving-vendor') {

                        return [
                            'permission_id' => $perm->id,
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
                'permissions' => $permissions->map(function ($perm) {
                    if ($perm->url == 'purchase-order' || $perm->url == 'OrderApproval' || $perm->url == 'MonitoringPO') {

                        return [
                            'permission_id' => $perm->id,
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
