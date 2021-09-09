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
        User::truncate();
        $datas = [
            [
                'username' => 'admin',
                'full_name' => 'Admin',
                'department' => 'Test',
                'phone_number' => '081',
                'npk' => '39748',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'type' => 0,
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ],
            [
                'username' => 'vendor',
                'full_name' => 'Vendor',
                'department' => 'Vendor',
                'phone_number' => '081',
                'npk' => '89352',
                'email' => 'vendor@example.com',
                'password' => Hash::make('password'),
                'type' => 1,
                'vendor_code' => '0000100097',
                'vendor_name' => 'INDONESIA STEEL TUBE WORKS PT',
                'created_by' => 'seeder',
                'updated_by' => 'seeder'
            ]
        ];

        $role = Role::first();

        foreach ($datas as $data) {

            $user = User::firstOrNew(['username' => $data['username']]);
            $user->username = $data['username'];
            $user->full_name = $data['full_name'];
            $user->department = $data['department'];
            $user->phone_number = $data['phone_number'];
            $user->npk = $data['npk'];
            $user->email = $data['email'];
            $user->password = $data['password'];
            $user->type = $data['type'];
            $user->role_id = $role->id;
            $user->role_name = $role->name;
            if ($data['type'] == 1){
                $user->vendor_code = $data['vendor_code'];
                $user->vendor_name = $data['vendor_name'];
            } else {
                $user->vendor_code = null;
                $user->vendor_name = null;
            }
            $user->photo = null;
            $user->reset_token = null;
            $user->api_token = null;
            $user->created_by = $data['created_by'];
            $user->updated_by = $data['updated_by'];
            $user->save();

        }
    }
}
