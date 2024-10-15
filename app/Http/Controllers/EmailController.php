<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\DynamicEmail;
use App\EmailTemplate;
use App\PurchaseOrder;
use App\EmailLog;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;

class EmailController extends Controller
{
    public function sendTestEmail(Request $request)
    {
        try {
            $emailTo = $request->input('email');

            $template = EmailTemplate::where('template_type', 'purchase_order_to_vendor')
                ->where('is_active', true)
                ->first();

            if (!$template) {
                return response()->json(['message' => 'Template not found'], 404);
            }

            $noPO = 'PO-02012';

            $data = [
                'userName' => 'John Doe',
                'orderNumber' => $noPO,
                'purchaseOrderLink' => 'http://127.0.0.1:580/dcci-po-tracker/?po=' . Crypt::encryptString($noPO)
            ];

            $bodyEmail = new DynamicEmail($template, $data);

            // Get the rendered email content as a string
            $emailContent = $bodyEmail->render();

            Mail::to($emailTo) // Get email from the request
                ->send(new DynamicEmail($template, $data));

            EmailLog::create([
                'recipient' => $emailTo,
                'subject' => 'Purchase Order Notification ' . $data['orderNumber'],
                'message' => $emailContent,
                'status' => 'sent',
            ]);

            return response()->json([
                'type' => 'success',
                'message' => 'Email sent successfully'
            ], 200);
        } catch (\Exception $e) {
            // Log the failed email
            EmailLog::create([
                'recipient' => $emailTo,
                'subject' => 'Purchase Order Notification ' . $data['orderNumber'],
                'message' => $emailContent ?? 'Failed to retrieve content',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'type' => 'error',
                'message' => 'Failed to send email: ' . $e->getMessage()
            ], 500);
        }
    }

    public function sendEmailPurchaseOrderConfirmation(Request $request, $po_number)
    {
        $template = EmailTemplate::where('template_type', 'purchase_order_to_vendor')
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return response()->json(['message' => 'Template not found'], 404);
        }

        $POData = PurchaseOrder::where('po_number', $po_number)->first();

        $data = [
            'userName' => $POData->supplier->name,
            'orderNumber' => $po_number,
            'totalAmount' => $POData->total_amount,
            'purchaseOrderLink' => env('FRONT_URL') . '/po/' . $po_number
        ];

        Mail::to($POData->delivery_email) // Get email from the request
            ->send(new DynamicEmail($template, $data));

        return response()->json(['message' => 'Email sent successfully']);
    }
}
