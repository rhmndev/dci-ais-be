<?php

use App\PurchaseOrder;
use App\PurchaseOrderItem;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

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
        for ($i = 0; $i < 10; $i++) {
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $faker->unique()->regexify('PO-[0-9]{5}'),
                'order_date' => $faker->date(),
                'delivery_email' => $faker->companyEmail,
                'delivery_date' => $faker->date(),
                'delivery_address' => $faker->address(),
                'supplier_id' => $faker->uuid(), // Assuming supplier_id is a UUID
                'supplier_code' => $faker->randomElement(['710519', '988938', '956459', '147510']), // Assuming supplier_id is a UUID
                'total_item_quantity' => $faker->randomFloat(2, 1, 100),
                'total_amount' => $faker->randomFloat(2, 100, 10000),
                'status' => $faker->randomElement(['approved']),
                'purchase_type' => $faker->word(),
                'created_by' => 'seeder', // Assuming created_by is a user ID (UUID)
                'updated_by' => 'seeder', // Assuming updated_by is a user ID (UUID)
                'purchase_agreement_by' => $faker->uuid(), // Assuming purchase_agreement_by is a user ID (UUID)
                'approved_at' => $faker->optional()->date(),
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
                'material_id' => $faker->uuid(), // Replace with your material ID generation logic
                'quantity' => $faker->randomNumber(2), // Random 2-digit quantity
                'unit_type' => $faker->randomElement(['pcs', 'pce', 'kg', 'L']), // Random unit type
                'unit_price' => $faker->randomFloat(2, 10, 500), // Random price between 10.00 and 500.00
                'unit_price_type' => $faker->randomElement(['IDR']), // Random unit type
            ]);
        }
    }
}
