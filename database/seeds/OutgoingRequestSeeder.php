<?php

use Illuminate\Database\Seeder;
use App\OutgoingRequest;
use Carbon\Carbon;

class OutgoingRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Sample outgoing requests based on the QR code from the image
        $outgoingRequests = [
            [
                'outgoing_no' => 'OUT-17900021',
                'po_number' => '5611002779',
                'supplier_name' => 'USAHA JAYA, PD',
                'supplier_code' => 'SUP001',
                'customer_name' => 'PT DHARMA CONTROLCABLE IND',
                'delivery_date' => '2025-10-28',
                'driver_name' => 'Driver AZIS',
                'current_status' => 'Pending',
                'qr_code' => 'OUT-17900021',
                'is_archived' => false,
                'items' => [
                    [
                        'part_number' => 'PART001',
                        'part_name' => 'Cable Assembly',
                        'quantity_request' => 12,
                        'quantity_delivered' => 0,
                        'unit' => 'PCS'
                    ],
                    [
                        'part_number' => 'PART002',
                        'part_name' => 'Connector',
                        'quantity_request' => 24,
                        'quantity_delivered' => 0,
                        'unit' => 'PCS'
                    ]
                ]
            ],
            [
                'outgoing_no' => 'OUT-17900022',
                'po_number' => '5611000373',
                'supplier_name' => 'USAHA JAYA, PD',
                'supplier_code' => 'SUP001',
                'customer_name' => 'PT TEST XYZ',
                'delivery_date' => '2025-10-28',
                'driver_name' => 'test',
                'current_status' => 'Pending',
                'qr_code' => 'OUT-17900022',
                'is_archived' => false,
                'items' => [
                    [
                        'part_number' => 'PART003',
                        'part_name' => 'Wire Harness',
                        'quantity_request' => 5,
                        'quantity_delivered' => 0,
                        'unit' => 'SET'
                    ]
                ]
            ],
            [
                'outgoing_no' => 'OUT-17900023',
                'po_number' => '43656456',
                'supplier_name' => 'PT TEST XYZ',
                'supplier_code' => 'SUP002',
                'customer_name' => 'PT TEST 2',
                'delivery_date' => '2025-10-29',
                'driver_name' => 'dqetre',
                'current_status' => 'Pending',
                'qr_code' => 'OUT-17900023',
                'is_archived' => false,
                'items' => [
                    [
                        'part_number' => 'PART004',
                        'part_name' => 'Terminal Block',
                        'quantity_request' => 8,
                        'quantity_delivered' => 0,
                        'unit' => 'PCS'
                    ]
                ]
            ]
        ];

        foreach ($outgoingRequests as $request) {
            OutgoingRequest::create($request);
        }

        $this->command->info('Outgoing requests seeded successfully!');
    }
}
