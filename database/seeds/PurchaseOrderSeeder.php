<?php

use App\PurchaseOrder;
use App\PurchaseOrderItem;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Carbon\Carbon;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PurchaseOrder::truncate();
        $faker = Faker::create();
        for ($i = 0; $i < 1000; $i++) {
            $type = $faker->randomElement(['pending', 'unapproved', 'approved']);
            switch ($type) {
                case 'unapproved':
                    $checkedBy = "39748";
                    $knowedBy = "39748";
                    $approvedBy = "39748";
                    $dateChecked = Carbon::now()->format('Y-m-d\TH:i:s.vP');
                    $dateKnowed = Carbon::now()->format('Y-m-d\TH:i:s.vP');
                    $dateApproved = Carbon::now()->format('Y-m-d\TH:i:s.vP');
                    break;

                case 'approved':
                    $checkedBy = "39748";
                    $knowedBy = "39748";
                    $approvedBy = "39748";
                    $dateChecked = Carbon::now()->format('Y-m-d\TH:i:s.vP');
                    $dateKnowed = Carbon::now()->format('Y-m-d\TH:i:s.vP');
                    $dateApproved = Carbon::now()->format('Y-m-d\TH:i:s.vP');
                    break;

                default:
                    $checkedBy = "";
                    $knowedBy = "";
                    $approvedBy = "";
                    $dateChecked = "";
                    $dateKnowed = "";
                    $dateApproved = "";
                    break;
            }
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $faker->unique()->regexify('PO-[0-9]{5}'),
                'user' => 'Admin',
                'user_npk' => '39748',
                'order_date' => Carbon::now()->format('Y-m-d\TH:i:s.vP'),
                'delivery_email' => $faker->companyEmail,
                'delivery_date' => Carbon::now()->format('Y-m-d\TH:i:s.vP'),
                'delivery_address' => $faker->address(),
                'supplier_id' => $faker->uuid(), // Assuming supplier_id is a UUID
                'supplier_code' => $faker->randomElement(['710519', '988938', '956459', '147510']), // Assuming supplier_id is a UUID
                'total_item_quantity' => $faker->randomFloat(2, 1, 100),
                'total_amount' => $faker->randomFloat(2, 100, 10000),
                'purchase_currency_type' => "IDR",
                'purchase_checked_by' => $checkedBy,
                'checked_at' => $dateChecked,
                'purchase_knowed_by' => $knowedBy,
                'knowed_at' => $dateKnowed,
                'purchase_agreement_by' => $approvedBy,
                'approved_at' => $dateApproved,
                'tax' => $faker->randomFloat(2, 100, 10000),
                'tax_type' => $faker->randomElement(['PPN']),
                'status' => $type,
                'is_send_email_to_supplier' => 0,
                'notes' => '',
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
            ]);

            $this->createPurchaseOrderItems($purchaseOrder, $faker);
        }
    }

    private function createPurchaseOrderItems(PurchaseOrder $purchaseOrder, $faker)
    {
        $numberOfItems = $faker->numberBetween(1, 5); // Create 1 to 5 items per order

        for ($j = 0; $j < $numberOfItems; $j++) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->_id,
                'material_id' => $faker->randomElement(['622ab8b35a0300005f001fae', '622ab8b35a0300005f001fb1', '622ab8b35a0300005f001fb0', '622ab8b35a0300005f001fb2', '622ab8b35a0300005f001fb3', '622ab8b35a0300005f001fb4', '622ab8b35a0300005f001fb5']), // Replace with your material ID generation logic
                'quantity' => $faker->randomNumber(2), // Random 2-digit quantity
                'unit_type' => $faker->randomElement(['pcs', 'pce', 'kg', 'L']), // Random unit type
                'unit_price' => $faker->randomFloat(2, 900000, 1000000), // Random price between 10.00 and 500.00
                'unit_price_type' => $faker->randomElement(['IDR']), // Random unit type
            ]);
        }
    }
}
