<?php

namespace App\Console\Commands;

use App\Http\Controllers\WhatsAppController;
use Illuminate\Console\Command;
use App\Jobs\SendWhatsAppReminder;
use App\Mail\PurchaseOrderEscalationReminder;
use App\PurchaseOrder;
use App\User;
use App\PurchaseOrderSigner;
use Illuminate\Support\Facades\Mail;
use App\Mail\PurchaseOrderReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendPurchaseOrderReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'purchase-order:send-reminder {is_testing? : Is testing (default: 0)} {send_type? : The status of the purchase order (need_assigned, need_assigned_hod) (default: need_assigned)}';

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
        Log::info('Starting purchase order reminder process.');
        $is_testing = $this->argument('is_testing') ?: 0;
        $send_type = $this->argument('send_type') ?: 'need_assigned';

        if ($is_testing) {
            $message = "";
            if ($send_type == 'need_assigned') {
                $message = "This is a test reminder for Purchase Orders that need to be assigned. Please check the system.";
            } else if ($send_type == 'need_assigned_hod') {
                $message = "This is a test reminder for Purchase Orders that need to be assigned to HOD. Please check the system.";
            }
            $PurchaseOrder = PurchaseOrder::first();
            $this->sendPendingEmailReminderTesting($PurchaseOrder);

            $recipientNumber = '6285156376462'; // Replace with the recipient's phone number
            WhatsAppController::sendWhatsAppMessage($recipientNumber, $message);
        } else {
            $this->sendDailyEmailReminders();
            $this->sendMinuteWhatsappReminders();
            $this->send7DayEscalationReminders();
        }

        // $this->info($resp);
        Log::info('Purchase order reminders processed successfully.');
        $this->info('Purchase order reminders sent successfully.');
    }

    private function sendDailyEmailReminders()
    {
        Log::info('Sending daily email reminders.');
        // Get POs needing reminders (created yesterday and status hasn't changed)
        $purchaseOrders = PurchaseOrder::where(function ($query) {
            $query->where('status', 'waiting for checking')
                ->orWhere('status', 'waiting for knowing')
                ->orWhere('status', 'waiting for approval');
        })->get();

        foreach ($purchaseOrders as $purchaseOrder) {
            try {
                $this->sendPendingEmailReminder($purchaseOrder);
                Log::info("Daily email reminder sent for PO: {$purchaseOrder->po_number}");
            } catch (\Exception $e) {
                Log::error("Error sending daily email reminder for PO: {$purchaseOrder->po_number} - {$e->getMessage()}");
            }
        }
    }

    private function sendMinuteWhatsappReminders()
    {
        Log::info('Sending 1-minute WhatsApp reminders.');

        // Get POs created in the last 1 minutes that need reminders
        $purchaseOrders = PurchaseOrder::where(function ($query) {
            $query->where('status', 'waiting for checking')
                ->orWhere('status', 'waiting for knowing')
                ->orWhere('status', 'waiting for approval');
        })
            ->get();

        foreach ($purchaseOrders as $purchaseOrder) {
            try {
                $message = $this->formatWhatsappMessage($purchaseOrder);
                $recipientNumber = '6285156376462'; // Replace with recipient's number
                WhatsAppController::sendWhatsAppMessage($recipientNumber, $message);
                Log::info("20-minute WhatsApp reminder sent for PO: {$purchaseOrder->po_number}");
            } catch (\Exception $e) {
                Log::error("Error sending 1-minute WhatsApp reminder for PO: {$purchaseOrder->po_number} - {$e->getMessage()}");
            }
        }
    }

    private function send7DayEscalationReminders()
    {
        Log::info('Sending 7-day escalation reminders.');
        // Get POs created 7 days ago that still need attention
        $purchaseOrders = PurchaseOrder::where('order_date', '>=', Carbon::now()->subDays(7)->startOfDay())
            ->where('order_date', '<', Carbon::now()->subDays(7)->endOfDay())
            ->where(function ($query) {
                $query->where('status', 'waiting for checking')
                    ->orWhere('status', 'waiting for knowing')
                    ->orWhere('status', 'waiting for approval');
            })
            ->get();

        foreach ($purchaseOrders as $purchaseOrder) {
            try {
                $message = $this->formatWhatsappMessage($purchaseOrder, true);
                $recipientNumber = '6285156376462'; // Replace with recipient's number
                WhatsAppController::sendWhatsAppMessage($recipientNumber, $message);
                $this->sendEscalationEmailReminder($purchaseOrder);
                Log::info("7-day escalation reminder sent for PO: {$purchaseOrder->po_number}");
            } catch (\Exception $e) {
                Log::error("Error sending 7-day escalation reminder for PO: {$purchaseOrder->po_number} - {$e->getMessage()}");
            }
        }
    }

    private function formatWhatsappMessage(PurchaseOrder $purchaseOrder, $isEscalation = false)
    {
        if ($isEscalation) {
            switch ($purchaseOrder->status) {
                case 'waiting for checking':
                    $message = "Purchase Order {$purchaseOrder->po_number} has been pending to needs to be checking for 7 days and requires your attention. Please check the system.";
                    break;
                case 'waiting for knowing':
                    $message = "Purchase Order {$purchaseOrder->po_number} has been pending to needs to be knowing for 7 days and requires your attention. Please check the system.";
                    break;
                case 'waiting for approval':
                    $message = "Purchase Order {$purchaseOrder->po_number} has been pending to needs to be approving for 7 days and requires your attention. Please check the system.";
                    break;
                default:
                    $message = "Purchase Order {$purchaseOrder->po_number} has been pending for 7 days and requires your attention. Please check the system.";
                    break;
            }
        } else {
            switch ($purchaseOrder->status) {
                case 'waiting for checking':
                    $message = "Purchase Order {$purchaseOrder->po_number} needs to be checking. Please check the system.";
                    break;
                case 'waiting for knowing':
                    $message = "Purchase Order {$purchaseOrder->po_number} needs to be knowing. Please check the system.";
                    break;
                case 'waiting for approval':
                    $message = "Purchase Order {$purchaseOrder->po_number} needs to be approving. Please check the system.";
                    break;
                default:
                    $message = "Purchase Order {$purchaseOrder->po_number} needs attention. Please check the system.";
                    break;
            }
        }
        // Customize the message based on the PO status

        return $message;
    }

    private function sendEscalationEmailReminder(PurchaseOrder $purchaseOrder)
    {
        // Get HOD's email address (you'll need to implement the logic to find this)
        $hodEmail = $this->getHODEmail($purchaseOrder); // Replace with your logic

        if ($hodEmail) {
            Mail::to($hodEmail)->send(new PurchaseOrderEscalationReminder($purchaseOrder));
        }
    }

    private function getHODEmail(PurchaseOrder $purchaseOrder)
    {
        // Implement logic to retrieve the HOD's email based on the purchase order
        // For example, you might:
        // 1. Get the user associated with the PO.
        // 2. Use the user's department or other information to find the HOD.
        // 3. Retrieve the HOD's email address.
        if ($purchaseOrder->status === 'waiting for knowing') {
            $signerType = 'knowed';
        } else if ($purchaseOrder->status === 'waiting for checking') {
            $signerType = 'checked';
        } else if ($purchaseOrder->status === 'waiting for approval') {
            $signerType = 'approved';
        } else {
            // Handle other statuses or log an error if needed
            return;
        }

        $recipientEmail = $this->getPurchaseOrderSignerEmail($signerType);

        // Placeholder - replace with your actual logic
        return $recipientEmail;
    }

    /**
     * Send email reminder for pending purchase order.
     *
     * @param  \App\PurchaseOrder  $purchaseOrder
     * @return void
     */
    private function sendPendingEmailReminder(PurchaseOrder $purchaseOrder)
    {
        // Find the recipient's email address (e.g., from the User model)
        if ($purchaseOrder->status === 'waiting for knowing') {
            $signerType = 'knowed';
        } else if ($purchaseOrder->status === 'waiting for checking') {
            $signerType = 'checked';
        } else if ($purchaseOrder->status === 'waiting for approval') {
            $signerType = 'approved';
        } else {
            // Handle other statuses or log an error if needed
            return;
        }

        $recipientEmails = $this->getPurchaseOrderSignerEmails($signerType);

        // Send the email reminder
        if ($recipientEmails->isNotEmpty()) {
            // Send the email reminder to each recipient
            Mail::to($recipientEmails)->send(new PurchaseOrderReminder($purchaseOrder));
        } else {
            // Handle case where signer email is not found (log error, etc.)
            Log::error("No recipient emails found for PO: {$purchaseOrder->po_number} with signer type: {$signerType}");
        }
    }

    private function getPurchaseOrderSignerEmails($signerType)
    {
        // Assuming you have a PurchaseOrderSigner model related to PurchaseOrder
        $POSigners = PurchaseOrderSigner::where('type', $signerType)->get();

        // Make sure to adjust the relationship and field names if needed
        return $POSigners->pluck('user.email')->filter();
    }

    private function sendPendingEmailReminderTesting(PurchaseOrder $purchaseOrder)
    {
        // Find the recipient's email address (e.g., from the User model)
        $recipientEmail = 'fachriansyah.10119065@mahasiswa.unikom.ac.id'; // Replace with actual email retrieval logic

        // Send the email reminder
        Mail::to($recipientEmail)->send(new PurchaseOrderReminder($purchaseOrder));
    }
}
