<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\PurchaseOrder;
use App\PurchaseOrderAnalytics;
use Carbon\Carbon;
use Tests\TestCase;

class CalculatePurchaseOrderAnalyticsTest extends TestCase
{
    public function test_it_calculates_purchase_order_analytics()
    {
        // Create test purchase orders
        $this->createPurchaseOrder('pending', now());
        $this->createPurchaseOrder('approved', now());
        $this->createPurchaseOrder('delivered', now()->subDays(5));
        $this->createPurchaseOrder('unapproved', now()->subDays(10));

        // Execute the command
        $this->artisan('purchase-order:calculate-analytics');

        // Assert that analytics are calculated correctly
        $analytics = PurchaseOrderAnalytics::where('month_year', now()->format('Y-m'))->first();
        $this->assertEquals(4, $analytics->total_orders);
        $this->assertEquals(1, $analytics->total_pending);
        $this->assertEquals(1, $analytics->total_approved);
        $this->assertEquals(1, $analytics->total_unapproved);
        $this->assertEquals(1, $analytics->total_delivered);
    }

    private function createPurchaseOrder($status, $createdAt)
    {
        PurchaseOrder::create([
            'po_number' => 'PO-12345',
            'order_date' => $createdAt,
            'status' => $status,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
