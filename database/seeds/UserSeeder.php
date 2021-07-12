<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $datas = [
            [
                'username' => 'admin',
                'password' => Hash::make('password'),
                'full_name' => 'Admin',
                'photo' => '',
                'api_token' => '',
                'created_by' => 'seeder',
                'changed_by' => 'seeder'
            ],
            [
                'username' => 'kelola',
                'password' => Hash::make('password'),
                'full_name' => 'Kelola',
                'photo' => '',
                'api_token' => '',
                'created_by' => 'seeder',
                'changed_by' => 'seeder'
            ]
        ];

        $role = Role::first();

        foreach ($datas as $data) {

            $user = User::firstOrNew(['username' => $data['username']]);
            $user->username = $data['username'];
            $user->password = $data['password'];
            $user->full_name = $data['full_name'];
            $user->photo = null;
            $user->api_token = null;
            $user->created_by = $data['created_by'];
            $user->changed_by = $data['changed_by'];
            $user->role_id = $role->id;
            $user->role_name = $role->name;
            $user->save();

        }
    }
}
