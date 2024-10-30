<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\DynamicEmail;
use App\EmailTemplate;
use App\PurchaseOrder;
use App\EmailLog;
use App\Http\Resources\PurchaseOrderResource;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Storage;

class EmailController extends Controller
{
    public function show(Request $request, $id)
    {
        $EmailTemplate = EmailTemplate::findOrFail($id);

        return response()->json([
            'type' => 'success',
            'data' =>  $EmailTemplate
        ]);
    }

    public function showTemplate(Request $request)
    {
        $request->validate([
            'template_type' => 'required|string',
        ]);

        try {
            $EmailTemplate = EmailTemplate::where('template_type', $request->template_type)->first();

            return response()->json([
                'type' => 'success',
                'data' =>  $EmailTemplate
            ]);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,

            ], 400);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'template_type' => 'required|string',
            'subject' => 'required|string',
            'body' => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        try {
            $EmailTemplate = EmailTemplate::findOrFail($id);

            $EmailTemplate->template_type = $request->template_type;
            $EmailTemplate->subject = $request->subject;
            $EmailTemplate->body = $request->body;
            $EmailTemplate->is_active = $request->is_active;

            $EmailTemplate->updated_by = auth()->user()->username;

            $EmailTemplate->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Data updated successfully!',
                'data' => NULL,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e . '.',
                'data' => NULL,

            ], 400);
        }
    }

    public function updateTemplate(Request $request, $id)
    {
        $request->validate([
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        try {
            $EmailTemplate = EmailTemplate::findOrFail($id);

            $EmailTemplate->from = $request->from;
            $EmailTemplate->cc = $request->cc;
            $EmailTemplate->bcc = $request->bcc;
            $EmailTemplate->subject = $request->subject;
            $EmailTemplate->body = $request->body;

            $EmailTemplate->updated_by = auth()->user()->username;

            $EmailTemplate->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Data updated successfully!',
                'data' => NULL,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,

            ], 400);
        }
    }

    public function store(Request $request)
    {

        $request->validate([
            'template_type' => 'required|string',
            'subject' => 'required|string',
            'body' => 'required|string',
            'is_active' => 'required|boolean',
        ]);

        try {

            $EmailTemplate = EmailTemplate::firstOrNew(['template_type' => $request->template_type]);
            $EmailTemplate->template_type = $request->template_type;
            $EmailTemplate->subject = $request->subject;
            $EmailTemplate->body = $request->body;
            $EmailTemplate->is_active = $request->is_active;

            $EmailTemplate->created_by = auth()->user()->username;
            $EmailTemplate->updated_by = auth()->user()->username;

            $EmailTemplate->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Data saved successfully!',
                'data' => NULL,
            ], 200);
        } catch (\Exception $e) {

            return response()->json([

                'type' => 'failed',
                'message' => 'Err: ' . $e->getMessage() . '.',
                'data' => NULL,

            ], 400);
        }
    }

    public function sendTestEmail(Request $request)
    {
        $noPO = "PO-91648";
        try {
            $ccTo = $request->input('cc') ? explode(',', $request->input('cc')) : [];
            $bccTo = $request->input('bcc') ? explode(',', $request->input('bcc')) : [];

            $template = EmailTemplate::where('template_type', 'purchase_order_to_vendor')
                ->where('is_active', true)
                ->first();

            if (!$template) {
                return response()->json(['message' => 'Template not found'], 404);
            }

            $POData = PurchaseOrder::where('po_number', $noPO)->first();

            $emailTo = $POData->delivery_email;

            // check if POData not signed
            if (isset($POData->is_knowed) && isset($POData->is_checked) && isset($POData->is_approved) && $POData->is_knowed == 1 && $POData->is_checked == 1 && $POData->is_approved == 1) {

                $data = [
                    'supplierName' => isset($POData->supplier) ? $POData->supplier->name : $POData->supplier_code,
                    'supplierCode' => isset($POData->supplier) ? $POData->supplier->code : $POData->supplier_code,
                    'orderDate' => $POData->order_date,
                    'deliveryDate' => $POData->delivery_date,
                    'totalAmount' => $POData->total_amount,
                    'orderNumber' => $noPO,
                    'purchaseOrderLink' => env('VENDOR_URL') . '/?view=' . Crypt::encryptString($noPO),
                    // 'cc' => ['fachriansyahmni@gmail.com', 'fachriansyah.10119065@mahasiswa.unikom.ac.id'],
                    // 'bcc' => $bccTo,
                ];

                // return response()->json([
                //     'type' => 'success',
                //     // 'data' =>  new PurchaseOrderResource($POData),
                //     'data2' => $data['cc']
                // ], 200);

                $pdf = PDF::loadView('purchase_orders.pdf2', ['purchaseOrder' => new PurchaseOrderResource($POData)]);
                $pdfContent = $pdf->output(); // Get the PDF content

                // 2. Store the PDF temporarily (optional but recommended)
                $pdfPath = 'temp/' . $noPO . '.pdf';
                Storage::put($pdfPath, $pdfContent);

                $bodyEmail = new DynamicEmail($template, $data);

                // Get the rendered email content as a string
                $emailContent = $bodyEmail->render();

                $attachments = [
                    // [
                    //     'path' => $pdfPath,
                    //     'name' => 'Purchase Order ' . $noPO . '.pdf', // Optional custom name
                    // ],
                ];


                Mail::to($emailTo) // Get email from the request
                    ->send(new DynamicEmail($template, $data, $attachments));

                EmailLog::create([
                    'recipient' => $emailTo,
                    'subject' => 'Purchase Order Notification ' . $data['orderNumber'],
                    'message' => $emailContent,
                    'status' => 'sent',
                ]);

                $POData->is_send_email_to_supplier = 1;
                $POData->save();

                return response()->json([
                    'type' => 'success',
                    'message' => 'Email sent successfully',
                    'data' => ''
                ], 200);
            } else {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Purchase Order ' . $POData->po_number . ' not signed',
                    'data' => ''
                ], 400);
            }
        } catch (\Exception $e) {
            // Log the failed email
            EmailLog::create([
                'recipient' => $emailTo,
                'subject' => 'Purchase Order Notification ' . $noPO,
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

    public static function sendEmailPurchaseOrderConfirmation(Request $request, $po_number)
    {
        $noPO = $po_number;
        try {
            $ccTo = $request->has('cc') ? explode(',', $request->cc) : [];
            $bccTo = $request->has('bcc') ? explode(',', $request->bcc) : [];

            $ccTo = array_filter($ccTo);
            $bccTo = array_filter($bccTo);
            if (!$request->has('body')) {
            } else {
            }

            $template = EmailTemplate::where('template_type', 'purchase_order_to_vendor')
                ->where('is_active', true)
                ->first();

            if (!$template) {
                return response()->json(['message' => 'Template not found'], 404);
            }

            $POData = PurchaseOrder::where('po_number', $noPO)->first();
            $deliveryEmail = $request->input('to') ? $request->to : $POData->delivery_email;
            $emailTo = $deliveryEmail;

            // check if POData not signed
            if (isset($POData->is_knowed) && isset($POData->is_checked) && isset($POData->is_approved) && $POData->is_knowed == 1 && $POData->is_checked == 1 && $POData->is_approved == 1) {

                $data = [
                    'supplierName' => isset($POData->supplier) ? $POData->supplier->name : $POData->supplier_code,
                    'supplierCode' => isset($POData->supplier) ? $POData->supplier->code : $POData->supplier_code,
                    'orderDate' => $POData->order_date,
                    'deliveryDate' => $POData->delivery_date,
                    'totalAmount' => $POData->total_amount,
                    'orderNumber' => $noPO,
                    'purchaseOrderLink' => env('FRONT_URL') . '/purchase-order/' . $POData->_id,
                    // 'cc' => $ccTo,
                    // 'bcc' => $bccTo,
                ];
                // $pdf = PDF::loadView('purchase_orders.pdf2', ['purchaseOrder' => new PurchaseOrderResource($POData)]);
                // $pdfContent = $pdf->output(); // Get the PDF content

                // // 2. Store the PDF temporarily (optional but recommended)
                // $pdfPath = 'temp/' . $noPO . '.pdf';
                // Storage::put($pdfPath, $pdfContent);

                $bodyEmail = new DynamicEmail($template, $data);

                // Get the rendered email content as a string
                $emailContent = $bodyEmail->render();

                $attachments = [
                    // [
                    //     'path' => $pdfPath,
                    //     'name' => 'Purchase Order ' . $noPO . '.pdf', // Optional custom name
                    // ],
                ];

                $Mailing = Mail::to($emailTo);

                if (!empty($ccTo)) {
                    $Mailing = $Mailing->cc($ccTo);
                }

                if (!empty($bccTo)) {
                    $Mailing = $Mailing->bcc($bccTo);
                }

                $Mailing->send(new DynamicEmail($template, $data, $attachments));

                EmailLog::create([
                    'recipient' => $emailTo,
                    'subject' => 'Purchase Order Notification ' . $data['orderNumber'],
                    'message' => $emailContent,
                    'status' => 'sent',
                ]);

                $POData->is_send_email_to_supplier = 1;
                $POData->save();

                return response()->json([
                    'type' => 'success',
                    'message' => 'Email sent successfully'
                ], 200);
            } else {
                return response()->json([
                    'type' => 'error',
                    'message' => 'Purchase Order ' . $POData->po_number . ' not signed'
                ], 400);
            }
        } catch (\Exception $e) {
            // Log the failed email
            EmailLog::create([
                'recipient' => $emailTo,
                'subject' => 'Purchase Order Notification ' . $noPO,
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
}
