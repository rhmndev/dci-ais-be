<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\PurchaseOrder;

class PurchaseOrderEscalationReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $purchaseOrder;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Purchase Order Escalation Reminder')->view('emails.purchase_order_escalation_reminder');
    }
}
