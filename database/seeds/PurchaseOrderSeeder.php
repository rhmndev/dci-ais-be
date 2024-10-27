<?php

use App\Material;
use App\PurchaseOrder;
use App\PurchaseOrderItem;
use App\Supplier;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use MongoDB\BSON\UTCDateTime;
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
        PurchaseOrderItem::truncate();
        $faker = Faker::create();
        for ($i = 0; $i < 1000; $i++) {
            $type = $faker->randomElement(['pending', 'unapproved', 'approved']);
            switch ($type) {
                case 'unapproved':
                    $checkedBy = "39748";
                    $knowedBy = "999988";
                    $approvedBy = "39748";
                    $dateChecked = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));
                    $dateKnowed = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));
                    $dateApproved = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));
                    $is_checked = true;
                    $is_knowed = true;
                    $is_approved = true;
                    break;

                case 'approved':
                    $checkedBy = "39748";
                    $knowedBy = "999988";
                    $approvedBy = "39748";
                    $dateChecked = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));
                    $dateKnowed = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));
                    $dateApproved = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));
                    $is_checked = true;
                    $is_knowed = true;
                    $is_approved = true;
                    break;

                default:
                    $checkedBy = "";
                    $knowedBy = "";
                    $approvedBy = "";
                    $dateChecked = null;
                    $dateKnowed = null;
                    $dateApproved = null;
                    $is_checked = false;
                    $is_knowed = false;
                    $is_approved = false;
                    break;
            }
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $faker->unique()->regexify('PO-[0-9]{5}'),
                'user' => 'Admin',
                'user_npk' => '39748',
                'order_date' => new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3)),
                'delivery_email' => $faker->companyEmail,
                'delivery_date' => new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3)),
                'delivery_address' => $faker->address(),
                'supplier_id' => $faker->uuid(), // Assuming supplier_id is a UUID
                'supplier_code' => $faker->randomElement(Supplier::pluck('code')->toArray()), // Assuming supplier_id is a UUID
                'total_item_quantity' => $faker->randomFloat(2, 1, 100),
                'total_amount' => $faker->randomFloat(2, 1000, 10000),
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
                'is_checked' => $is_checked,
                'is_knowed' => $is_knowed,
                'is_approved' => $is_approved,
                'notes' => '',
                'notes_from_checker' => '',
                'notes_from_knower' => '',
                'notes_from_approver' => '',
                'created_by' => 'seeder',
                'updated_by' => 'seeder',
            ]);

            $this->createPurchaseOrderItems($purchaseOrder, $faker);
        }
    }

    private function createPurchaseOrderItems(PurchaseOrder $purchaseOrder, $faker)
    {
        $numberOfItems = $faker->numberBetween(1, 5); // Create 1 to 5 items per order
        $materialIds = Material::pluck('_id')->take(10)->toArray();
        for ($j = 0; $j < $numberOfItems; $j++) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->_id,
                'material_id' => $faker->randomElement($materialIds), // Replace with your material ID generation logic
                'quantity' => $faker->randomNumber(2), // Random 2-digit quantity
                'unit_type' => $faker->randomElement(['pcs', 'pce', 'kg', 'L']), // Random unit type
                'unit_price' => $faker->randomFloat(2, 900000, 1000000), // Random price between 10.00 and 500.00
                'unit_price_type' => $faker->randomElement(['IDR']), // Random unit type
                'unit_price_amount' => $faker->randomFloat(2, 900000, 1000000), // Random price between 10.00 and 500.00
            ]);
        }
    }
}
