<?php

namespace App\Jobs;

use App\Http\Controllers\WhatsAppController;
use App\PurchaseOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $purchaseOrder;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!$this->purchaseOrder->is_knowed || !$this->purchaseOrder->is_checked || !$this->purchaseOrder->is_approved) {
            $message = "Purchase Order {$this->purchaseOrder->po_number} needs to be assigned. Please check the system.";
            $recipientNumber = 'whatsapp:+6285156376462'; // Replace with the recipient's phone number

            // Send the WhatsApp message
            WhatsAppController::sendWhatsAppMessage($recipientNumber, $message);

            Log::info("WhatsApp reminder sent for PO {$this->purchaseOrder->po_number}");
        } else {
            Log::info("PO {$this->purchaseOrder->po_number} is already assigned. Skipping reminder.");
        }
    }
}
