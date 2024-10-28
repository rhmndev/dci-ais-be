<?php

namespace App\Console\Commands;

use App\Http\Controllers\WhatsAppController;
use Illuminate\Console\Command;
use App\Jobs\SendWhatsAppReminder;
use App\PurchaseOrder;

class SendPurchaseOrderReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-order:send-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'PO Send Reminder Whatsapp';

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
        $unassignedPurchaseOrders = PurchaseOrder::where(function ($query) {
            $query->where('is_knowed', false)
                ->orWhere('is_checked', false)
                ->orWhere('is_approved', false);
        })->get();

        foreach ($unassignedPurchaseOrders as $purchaseOrder) {
            // WhatsAppController::sendWhatsAppMessage("whatsapp:+61234567", $purchaseOrder->po_number);
            // SendWhatsAppReminder::dispatch($purchaseOrder);
            if (!$purchaseOrder->is_knowed || !$purchaseOrder->is_checked || !$purchaseOrder->is_approved) {
                $message = "Purchase Order {$purchaseOrder->po_number} needs to be assigned. Please check the system.";
                $recipientNumber = 'whatsapp:+62123123123'; // Replace with the recipient's phone number

                // Send the WhatsApp message
                WhatsAppController::sendWhatsAppMessage($recipientNumber, $message);
            }
        }

        // $this->info($resp);
        $this->info('Purchase order reminders sent successfully.');
    }
}
