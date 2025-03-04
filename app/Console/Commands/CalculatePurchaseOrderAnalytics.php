<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\PurchaseOrder;
use App\PurchaseOrderAnalytics;
use Carbon\Carbon;

class CalculatePurchaseOrderAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-order:calculate-analytics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate monthly purchase order analytics';

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
        // Get the beginning of the current month
        $startOfMonth = Carbon::now()->startOfMonth();

        // print to console
        // $this->info('Calculating purchase order analytics for ' . $startOfMonth->format('F Y'));
        // $startOfMonth
        // Calculate analytics for the current month
        $this->calculateAndStoreAnalytics($startOfMonth);

        // You can optionally calculate analytics for past months as well
        // For example, to calculate for the last 3 months:
        // for ($i = 1; $i <= 3; $i++) {
        //     $this->calculateAndStoreAnalytics($startOfMonth->subMonth($i));
        // }

        return 0;
    }

    /**
     * Calculate and store purchase order analytics for a given month.
     *
     * @param  Carbon  $monthYear
     * @return void
     */
    private function calculateAndStoreAnalytics(Carbon $monthYear)
    {
        // $analytics = PurchaseOrder::where('order_date', '>=', $monthYear)
        //     ->where('order_date', '<', $monthYear->copy()->addMonth())
        //     ->selectRaw('COUNT(*) as total_orders')
        //     ->selectRaw('SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as total_pending')
        //     ->selectRaw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as total_approved')
        //     ->selectRaw('SUM(CASE WHEN status = "unapproved" THEN 1 ELSE 0 END) as total_unapproved')
        //     ->selectRaw('SUM(CASE WHEN status = "delivered" THEN 1 ELSE 0 END) as total_delivered')
        //     ->first();
        $analytics = PurchaseOrder::raw(function ($collection) use ($monthYear) {
            return $collection->aggregate([
                [
                    '$match' => [
                        'order_date' => [
                            '$gte' => new \MongoDB\BSON\UTCDateTime($monthYear->startOfMonth()->getTimestamp() * 1000),
                            '$lt' => new \MongoDB\BSON\UTCDateTime($monthYear->endOfMonth()->getTimestamp() * 1000),
                        ],
                    ],
                ],
                [
                    '$group' => [
                        '_id' => null,
                        'total_orders' => ['$sum' => 1],
                        'total_pending' => ['$sum' => ['$cond' => [['$eq' => ['$status', 'pending']], 1, 0]]],
                        'total_approved' => ['$sum' => ['$cond' => [['$eq' => ['$status', 'approved']], 1, 0]]],
                        'total_unapproved' => ['$sum' => ['$cond' => [['$eq' => ['$status', 'unapproved']], 1, 0]]],
                        'total_delivered' => ['$sum' => ['$cond' => [['$eq' => ['$status', 'delivered']], 1, 0]]],
                    ],
                ],
                [
                    '$project' => [
                        '_id' => 0, // Exclude the _id field
                        'total_orders' => 1,
                        'total_pending' => 1,
                        'total_approved' => 1,
                        'total_unapproved' => 1,
                        'total_delivered' => 1,
                    ],
                ],
            ]);
        });

        $this->info("total data: " . $analytics);

        try {
            PurchaseOrderAnalytics::updateOrCreate(
                ['month_year' => $monthYear->format('Y-m')],
                [
                    'total_orders' => $analytics[0]['total_orders'] ?? 0,
                    'total_pending' => $analytics[0]['total_pending'] ?? 0,
                    'total_approved' => $analytics[0]['total_approved'] ?? 0,
                    'total_unapproved' => $analytics[0]['total_unapproved'] ?? 0,
                    'total_delivered' => $analytics[0]['total_delivered'] ?? 0,
                ]
            );
        } catch (\Throwable $th) {
            $this->info($th->getMessage());
        }
    }
}
