<?php

use App\Customer;
use App\PurchaseOrder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(PermissionSeeder::class);
        $this->call(RoleSeeder::class);
        // $this->call(VendorSeeder::class);
        // $this->call(MaterialSeeder::class);
        $this->call(ScaleSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(CustomerSeeder::class);
        $this->call(StatusPartComponentSeeder::class);
        $this->call(LineNumberSeeder::class);
        $this->call(SupplierSeeder::class);
        $this->call(EmailTemplateSeeder::class);
        $this->call(PurchaseOrder::class);
    }
}
