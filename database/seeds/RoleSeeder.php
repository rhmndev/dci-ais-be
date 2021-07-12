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

        $role = new Role;
        $role->name = 'Admin';
        $role->description = 'Administrator';
        $role->permissions = $permissions->map(function($perm){
            return [
                'permission_id' => $perm->id,
                'allow' => true
            ];
        })->toArray();
        $role->created_by = 'seeder';
        $role->changed_by = 'seeder';
        $role->save();
    }
}
