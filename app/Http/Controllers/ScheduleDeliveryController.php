<?php

namespace App\Http\Controllers;

use App\PurchaseOrderScheduleDelivery;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ScheduleDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'columns' => 'required',
            'perpage' => 'required|numeric',
            'page' => 'required|numeric',
            'sort' => 'required|string',
            'po_number' => 'nullable|string',
            'order' => 'string',
        ]);

        $user = auth()->user();

        $keyword = ($request->keyword != null) ? $request->keyword : '';
        $order = ($request->order != null) ? $request->order : 'ascend';
        $po_number = ($request->po_number != null) ? $request->po_number : '';
        try {

            $scheduleDelivery = new PurchaseOrderScheduleDelivery;
            $data = array();
            $resultAlls = $scheduleDelivery->getAllData($keyword, $request->columns, $request->sort, $order, $po_number);
            $results = $scheduleDelivery->getData($keyword, $request->columns, $request->perpage, $request->page, $request->sort, $order, $po_number);

            return response()->json([
                'type' => 'success',
                'data' => $results,
                'dataAll' => $resultAlls,
                'total' => count($resultAlls),
            ], 200);
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
            'file' => 'nullable|mimes:xlsx,csv,xls|max:2048',
            'description' => 'nullable|string',
            'show_to_supplier' => 'required|boolean',
            'is_send_email_to_supplier' => 'required|boolean',
        ]);

        $scheduleDelivery = PurchaseOrderScheduleDelivery::findOrFail($id);

        try {
            if ($request->hasFile('file')) {
                // Delete the old file if it exists
                if (Storage::disk('public')->exists($scheduleDelivery->file_path)) {
                    Storage::disk('public')->delete($scheduleDelivery->file_path);
                }

                $file = $request->file('file');
                $originalFileName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileNameWithoutExtension = pathinfo($originalFileName, PATHINFO_FILENAME);
                $fileNameSlug = Str::slug($fileNameWithoutExtension, '-');
                $fileName = $fileNameSlug . '_' . time() . '.' . $extension;
                $filePath = $file->storeAs('schedule_deliveries', $fileName, 'public');

                $scheduleDelivery->filename = $fileName;
                $scheduleDelivery->supplier_revised_file_path = $filePath;
            }

            // Update other fields
            $scheduleDelivery->status_schedule = $request->status_schedule;
            if ($request->status_schedule == 'confirmed') {
                if ($scheduleDelivery->supplier_revised_file_path != null) {
                    Storage::disk('public')->copy($scheduleDelivery->supplier_revised_file_path, $scheduleDelivery->file_path);
                    Storage::disk('public')->delete($scheduleDelivery->supplier_revised_file_path);

                    $scheduleDelivery->supplier_revised_file_path = null;
                }

                $scheduleDelivery->supplier_confirmed = $request->status_schedule;
                $scheduleDelivery->supplier_confirmed_at = Carbon::now();
            } else if ($request->status_schedule == 'revision_requested') {
                $scheduleDelivery->supplier_confirmed = 'revision_needed';
                $scheduleDelivery->supplier_revision_notes = $request->notes;
                $scheduleDelivery->supplier_confirmed_at = Carbon::now();

                // adding send email to internal for revision
                EmailController::sendEmailForRevision($scheduleDelivery);
            }
            $scheduleDelivery->description = $request->description;
            $scheduleDelivery->show_to_supplier = $request->show_to_supplier;
            $scheduleDelivery->updated_by = auth()->user()->role_name === 'supplier' ? auth()->user()->full_name : auth()->user()->npk;
            $scheduleDelivery->save();

            if (isset($request->status_schedule) && $request->status_schedule == 'confirmed') {
                $scheduleDelivery->po->po_status = 'open';
                $scheduleDelivery->po->save();
            }

            if (auth()->user()->role_name !== 'supplier') {
                if ($request->is_send_email_to_supplier) {
                    EmailController::sendEmailPurchaseOrderSchedule($request, $scheduleDelivery->po_number);
                }
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Schedule delivery updated successfully',
                'data' => $scheduleDelivery,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'failed',
                'message' => 'Error: ' . $th->getMessage(),
                'data' => NULL,
            ], 200);
        }
        // Handle file upload if a new file is provided
    }

    public function updateConfirmationScheduleDelivery(Request $request, $id)
    {
        $scheduleDelivery = PurchaseOrderScheduleDelivery::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'supplier_confirmed' => 'required|in:confirmed,revision_needed', // Validate the confirmation status
            'supplier_revision_notes' => 'nullable|string', // Notes are optional
            'file' => 'nullable|mimes:xlsx,csv,xls|max:2048', // Optional revised file upload
        ]);


        if ($validator->fails()) {
            return response()->json([
                'type' => 'error',
                'message' => $validator->errors()->first(),
                'data' => $request->all()
            ], 422);
        }

        $scheduleDelivery->supplier_confirmed = $request->supplier_confirmed;
        $scheduleDelivery->supplier_revision_notes = $request->supplier_revision_notes;
        $scheduleDelivery->supplier_confirmed_at = new UTCDateTime(Carbon::parse(Carbon::now()->format('Y-m-d H:i:s'))->getPreciseTimestamp(3));



        // Handle revised file upload
        if ($request->hasFile('file')) {

            // If a previous revision exists, delete it.  Consider a better archival strategy in production.
            if ($scheduleDelivery->supplier_revised_file_path && Storage::disk('public')->exists($scheduleDelivery->supplier_revised_file_path)) {
                Storage::disk('public')->delete($scheduleDelivery->supplier_revised_file_path);
            }


            $file = $request->file('file');
            $originalFileName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileNameWithoutExtension = pathinfo($originalFileName, PATHINFO_FILENAME);
            $fileNameSlug = Str::slug($fileNameWithoutExtension, '-');
            $fileName = $fileNameSlug . '_' . time() . '.' . $extension;
            $filePath = $file->storeAs('schedule_deliveries/revised', $fileName, 'public'); // Store in a "revised" subfolder
            $scheduleDelivery->supplier_revised_file_path = $filePath;
        }

        $scheduleDelivery->save();


        return response()->json([
            'type' => 'success',
            'message' => 'Schedule delivery confirmation updated successfully',
            'data' => $scheduleDelivery,
        ], 200);
    }

    public function downloadScheduleDelivery($id)
    {
        // Ensure the file exists and the user is authorized to access it
        $scheduleDelivery = PurchaseOrderScheduleDelivery::findOrFail($id);

        // Get the file path from the schedule delivery record
        $filePath = $scheduleDelivery->file_path;

        // Ensure the file exists and the user is authorized to access it
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath);
        } else {
            abort(404, 'File not found');
        }
    }
    public function downloadRevisionScheduleDelivery($id)
    {
        // Ensure the file exists and the user is authorized to access it
        $scheduleDelivery = PurchaseOrderScheduleDelivery::findOrFail($id);

        // Get the file path from the schedule delivery record
        $filePath = $scheduleDelivery->supplier_revised_file_path;

        // Ensure the file exists and the user is authorized to access it
        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->download($filePath);
        } else {
            abort(404, 'File not found');
        }
    }

    public function destroy($id)
    {
        try {
            $scheduleDelivery = PurchaseOrderScheduleDelivery::findOrFail($id);

            if (Storage::disk('public')->exists($scheduleDelivery->file_path)) {
                Storage::disk('public')->delete($scheduleDelivery->file_path);
            }

            $scheduleDelivery->delete();

            $scheduleDelivery->po->po_status = 'waiting for schedule delivery';
            $scheduleDelivery->po->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Schedule delivery deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error deleting schedule delivery: ' . $e->getMessage(),
            ], 500);
        }
    }
}
