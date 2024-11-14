<?php

namespace App\Http\Controllers;

use App\PurchaseOrderScheduleDelivery;
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
        return response()->json([
            'type' => 'error',
            'message' => '',
            'data' => $request->all()
        ], 422);
        $scheduleDelivery = PurchaseOrderScheduleDelivery::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'file' => 'nullable|mimes:xlsx,csv,xls|max:2048',
            'description' => 'nullable|string',
            'show_to_supplier' => 'required|boolean',
            'is_send_email_to_supplier' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'type' => 'error',
                'message' => $validator->errors()->first(),
                'data' => $request->all()
            ], 422);
        }

        // Handle file upload if a new file is provided
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
            $scheduleDelivery->file_path = $filePath;
        }

        // Update other fields
        $scheduleDelivery->description = $request->description;
        $scheduleDelivery->show_to_supplier = $request->show_to_supplier;
        $scheduleDelivery->updated_by = auth()->user()->npk;
        $scheduleDelivery->save();

        if ($request->is_send_email_to_supplier) {
            EmailController::sendEmailPurchaseOrderSchedule($request, $scheduleDelivery->po_number);
        }

        return response()->json([
            'type' => 'success',
            'message' => 'Schedule delivery updated successfully',
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
