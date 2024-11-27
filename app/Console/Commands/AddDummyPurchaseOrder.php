<?php

namespace App\Console\Commands;

use App\Material;
use App\PurchaseOrder;
use App\PurchaseOrderItem;
use App\ShippingAddress;
use App\SLock;
use App\Supplier;
use Illuminate\Console\Command;
use Faker\Factory as Faker;
use MongoDB\BSON\UTCDateTime;
use Carbon\Carbon;

class AddDummyPurchaseOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-order:add-dummy {numberOfOrders? : The number of dummy orders to create (default: 1)}
    {status? : The status of the purchase order (pending, waiting for checking, waiting for knowing, waiting for approval, approved, unapproved) (default: approved)} {supplier_code? : The number of supplier code} {numberOfItems? : The number of items per order}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new dummy purchase order every minute';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $numberOfOrders = $this->argument('numberOfOrders') ?: 1;
        $supplier_code = $this->argument('supplier_code') ?: "";
        $status = $this->argument('status') ?: 'approved';
        $validStatuses = ['pending', 'approved', 'unapproved', 'waiting for checking', 'waiting for knowing', 'waiting for approval', 'waiting for schedule delivery'];
        if (!in_array($status, $validStatuses)) {
            $this->error("Invalid status. Choose from: " . implode(', ', $validStatuses));
            return 1;
        }

        $faker = Faker::create();
        for ($i = 0; $i < $numberOfOrders; $i++) {
            switch ($status) {
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
                case 'waiting for knowing':
                    $checkedBy = "39748";
                    $knowedBy = "";
                    $approvedBy = "";
                    $dateChecked = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));
                    $dateKnowed = null;
                    $dateApproved = null;
                    $is_checked = true;
                    $is_knowed = false;
                    $is_approved = false;
                    break;
                case 'waiting for approval':
                    $checkedBy = "39748";
                    $knowedBy = "39748";
                    $approvedBy = "";
                    $dateChecked = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));
                    $dateKnowed = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));
                    $dateApproved = null;
                    $is_checked = true;
                    $is_knowed = true;
                    $is_approved = false;
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

            $supplierCode = $supplier_code != "" ? $supplier_code : $faker->randomElement(Supplier::pluck('code')->toArray());
            $supplier = Supplier::where('code', $supplierCode)->first();

            if (!$supplier) {
                $this->error("Supplier with code '{$supplierCode}' not found.");
                return 1;
            }

            $purchaseOrder = PurchaseOrder::create([
                'plant_number' => "1601",
                'po_number' => $faker->unique()->regexify('PO-[0-9]{5}'),
                'user' => 'Admin',
                'user_npk' => '39748',
                'order_date' => new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3)),
                'delivery_email' => $supplier->emails,
                'delivery_date' => new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3)),
                'delivery_address' => $faker->randomElement(ShippingAddress::pluck('full_address')->toArray()),
                'supplier_id' => $faker->uuid(),
                'supplier_code' => $supplier_code != "" ? $supplier_code : $supplier->code,
                's_locks_code' => $faker->randomElement(SLock::pluck('code')->toArray()),
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
                'status' => $status,
                'po_status' => $status === 'approved'  ? 'waiting for schedule delivery' : 'in progress',
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

        $this->info("{$numberOfOrders} dummy purchase orders added!");
        return 0;
    }

    private function createPurchaseOrderItems(PurchaseOrder $purchaseOrder, $faker)
    {
        $numberOfItems = $this->argument('numberOfItems') ?: $faker->numberBetween(1, 5);
        $materialIds = Material::pluck('_id')->take(10)->toArray();
        for ($j = 0; $j < $numberOfItems; $j++) {
            $quantity = $this->ask("Enter quantity for item " . ($j + 1) . ":");
            if (!$quantity) {
                $quantity = $faker->randomElement([1000, 1500, 2000, 2500, 3000, 3500, 4000]);
            }

            PurchaseOrderItem::create([
                'purchase_order_id' => $purchaseOrder->_id,
                'material_id' => $faker->randomElement($materialIds),
                'quantity' => $quantity,
                'unit_type' => $faker->randomElement(['pcs', 'pce']),
                'unit_price' => $faker->randomFloat(2, 900000, 1000000),
                'unit_price_type' => $faker->randomElement(['IDR']),
                'unit_price_amount' => $faker->randomFloat(2, 900000, 1000000),
            ]);
        }
    }
}
