<?php

namespace App\Console\Commands;

use App\Material;
use App\PurchaseOrder;
use App\PurchaseOrderItem;
use App\Supplier;
use Illuminate\Console\Command;
use SAPNWRFC\Connection as SAPConnection; // Import the SAP connection class (composer require saprfc/saprfc)


class SyncSapPurchaseOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sap:sync-purchase-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync purchase orders from SAP';

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
        // 1. Connect to SAP
        $connection = $this->connectToSAP();

        // 2. Fetch Purchase Orders from SAP
        $sapPurchaseOrders = $this->getPurchaseOrdersFromSAP($connection);

        // 3. Process and Sync Each Order
        foreach ($sapPurchaseOrders as $sapOrder) {
            $this->syncPurchaseOrder($sapOrder);
        }

        // 4. Close SAP Connection (if needed)
        $connection->close();

        $this->info('Purchase orders synced successfully!');
    }

    private function connectToSAP()
    {
        // Replace with your actual SAP connection parameters
        $config = [
            'ashost' => 'your-sap-host',
            'sysnr'  => 'your-sap-system-number',
            'client' => 'your-sap-client',
            'user'   => 'your-sap-user',
            'passwd' => 'your-sap-password',
        ];

        try {
            return new SAPConnection($config);
        } catch (\Exception $e) {
            $this->error("Error connecting to SAP: " . $e->getMessage());
            die(); // Or handle the error more gracefully
        }
    }

    private function getPurchaseOrdersFromSAP(SAPConnection $connection)
    {
        // Call your SAP RFC function module
        $function = $connection->getFunction('Z_YOUR_RFC_FUNCTION_MODULE'); // Replace with your RFC function name
        $options = [
            // ... any necessary input parameters for the RFC function
        ];
        $result = $function->invoke($options);

        // Process the $result from the RFC call to extract purchase order data
        // ... (This will depend on the structure of your RFC function's output)

        return $processedSapOrders; // Return an array of processed SAP order data
    }

    private function syncPurchaseOrder($sapOrder)
    {
        // 1. Find or Create Supplier
        $supplier = Supplier::firstOrCreate(
            ['code' => $sapOrder['supplier_code']], // Assuming 'supplier_code' is in the SAP data
            ['name' => $sapOrder['supplier_name']] // And 'supplier_name'
        );

        // 2. Find or Create Material (for each item in the order)
        // ... (Similar to Supplier, but for Material)

        // 3. Create or Update Purchase Order
        $purchaseOrder = PurchaseOrder::updateOrCreate(
            ['po_number' => $sapOrder['po_number']], // Assuming 'po_number' is unique
            [
                'supplier_id' => $supplier->id,
                'order_date' => $sapOrder['order_date'],
                // ... map other fields from $sapOrder
            ]
        );

        // 4. Create or Update Purchase Order Items
        // ... (Iterate through items in $sapOrder and create/update PurchaseOrderItem records)
    }
}
