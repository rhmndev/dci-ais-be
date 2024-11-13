<?php

namespace App\Console\Commands;

use App\EmailLog;
use App\EmailTemplate;
use App\Mail\DynamicEmail;
use App\PurchaseOrder;
use App\Role;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPurchaseOrderConfirmationEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-po-confirmation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send purchase order confirmation email to supplier';

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
        $purchaseOrders = PurchaseOrder::where('status', 'approved')
            ->where('is_send_email_to_supplier', 0)
            ->get();

        foreach ($purchaseOrders as $POData) {
            $this->sendConfirmationEmail($POData);
        }

        return 0;
    }

    private function sendConfirmationEmail($POData)
    {
        try {
            $template = EmailTemplate::where('template_type', 'purchase_order_to_vendor')
                ->where('is_active', true)
                ->first();

            $noPO = $POData->po_number;
            $emailTo = $POData->delivery_email;

            $data = [
                'supplierName' => isset($POData->supplier) ? $POData->supplier->name : $POData->supplier_code,
                'supplierCode' => isset($POData->supplier) ? $POData->supplier->code : $POData->supplier_code,
                'orderDate' => $POData->order_date,
                'deliveryDate' => $POData->delivery_date,
                'totalAmount' => $POData->total_amount,
                'orderNumber' => $noPO,
                'purchaseOrderLink' => env('FRONT_URL') . '/purchase-order/' . $POData->_id,
                'purchaseOrderLinkInternal' => env('FRONT_URL') . '/purchase-order/' . $POData->_id,
            ];

            $bodyEmail = new DynamicEmail($template, $data);
            $emailContent = $bodyEmail->render();

            // Send to supplier
            Mail::to($emailTo)->send(new DynamicEmail($template, $data));

            $this->sendInternalConfirmationEmails($POData, $data);

            // Log the email
            EmailLog::create([
                'recipient' => $emailTo,
                'subject' => 'Purchase Order Notification ' . $data['orderNumber'],
                'message' => $emailContent,
                'status' => 'sent',
            ]);

            // Update the PO to indicate that the email has been sent
            $POData->is_send_email_to_supplier = 1;
            $POData->po_status = "open";
            $POData->save();

            Log::info("Purchase order confirmation email sent for PO: {$noPO}");
        } catch (\Exception $e) {
            // Log the failed email
            EmailLog::create([
                'recipient' => $emailTo,
                'subject' => 'Purchase Order Notification ' . $noPO,
                'message' => $emailContent ?? 'Failed to retrieve content',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            Log::error("Error sending PO confirmation email for PO: {$noPO} - {$e->getMessage()}");
        }
    }

    private function sendInternalConfirmationEmails($POData, $data)
    {
        $templateInternal = EmailTemplate::where('template_type', 'purchase_order_approved_to_internal')
            ->where('is_active', true)
            ->first();

        $templateInternalSendSchedule = EmailTemplate::where('template_type', 'purchase_order_approved_need_schedule_to_internal')
            ->where('is_active', true)
            ->first();

        if (!$templateInternal || !$templateInternalSendSchedule) {
            Log::error('Email template not found for internal purchase order confirmation.');
            return;
        }

        // Send to specific internal email
        $emailInternal = "fachriansyahmni@gmail.com";
        Mail::to($emailInternal)->send(new DynamicEmail($templateInternal, $data));

        // Send to Warehouse users
        $warehouseRole = Role::where('name', 'Warehouse')->first();
        if (!$warehouseRole) {
            Log::error('Warehouse role not found for internal purchase order confirmation.');
            return;
        }

        $internalWarehouseUsers = User::where('role_name', 'Warehouse')->get();
        $emailInternalSendSchedule = $internalWarehouseUsers->pluck('email')->toArray();

        Mail::to($emailInternalSendSchedule)->send(new DynamicEmail($templateInternalSendSchedule, $data));
    }
}
