<?php

namespace App\Http\Controllers;

use App\Material;
use App\OutgoingGood;
use App\OutgoingGoodItem;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OutgoingGoodController extends Controller
{
    /**
     * Display a listing of outgoing goods
     */
    public function index(Request $request)
    {
        $query = OutgoingGood::with(['items', 'assignedTo']);

        // Filter by assignment status
        if ($request->has('is_assigned')) {
            $query->where('is_assigned', $request->is_assigned);
        }

        // Filter by completion status
        if ($request->has('is_completed')) {
            $query->where('is_completed', $request->is_completed);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $outgoingGoods = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $outgoingGoods
        ]);
    }

    /**
     * Store a newly created outgoing good
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'priority' => 'required|in:low,normal,high,urgent',
            'outgoing_location' => 'required|string',
            'handle_for' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.material_code' => 'required|string|exists:materials,code',
            'items.*.quantity_needed' => 'required|numeric|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate a unique reference number
        $refNumber = 'OG-' . date('Ymd') . '-' . Str::random(6);

        $outgoingGood = new OutgoingGood();
        $outgoingGood->number = $refNumber;
        $outgoingGood->date = $request->date;
        $outgoingGood->priority = $request->priority;
        $outgoingGood->outgoing_location = $request->outgoing_location;
        $outgoingGood->handle_for = $request->handle_for;
        $outgoingGood->status = 'pending';
        $outgoingGood->is_assigned = false;
        $outgoingGood->is_completed = false;
        $outgoingGood->created_by = auth()->user()->npk;
        $outgoingGood->notes = $request->notes;
        $outgoingGood->save();

        // Save items
        foreach ($request->items as $item) {
            $material = Material::where('code', $item['material_code'])->first();

            $outgoingItem = new OutgoingGoodItem();
            $outgoingItem->outgoing_good_id = $outgoingGood->id;
            $outgoingItem->material_code = $item['material_code'];
            $outgoingItem->material_name = $material->description;
            $outgoingItem->quantity_needed = $item['quantity_needed'];
            $outgoingItem->quantity_out = 0;
            $outgoingItem->uom_needed = $material->unit;
            $outgoingItem->uom_out = $material->unit;
            $outgoingItem->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Outgoing request created successfully',
            'data' => $outgoingGood->load('items')
        ], 201);
    }

    /**
     * Display the specified outgoing good
     */
    public function show($id)
    {
        $outgoingGood = OutgoingGood::with(['items', 'assignedTo', 'completedBy'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $outgoingGood
        ]);
    }

    /**
     * Assign outgoing goods to a user
     */
    public function assign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'outgoing_ids' => 'required|array|min:1',
            'outgoing_ids.*' => 'required|exists:outgoing_goods,id',
            'user_id' => 'required|exists:users,id',
            'user_type' => 'required|in:internal,external'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::findOrFail($request->user_id);

        // Check if user type matches
        if (($request->user_type === 'internal' && $user->type_id !== 0) ||
            ($request->user_type === 'external' && $user->type_id !== 1)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'User type does not match the selected type'
            ], 422);
        }

        foreach ($request->outgoing_ids as $id) {
            $outgoingGood = OutgoingGood::findOrFail($id);

            // Check if already assigned
            if ($outgoingGood->is_assigned) {
                continue;
            }

            $outgoingGood->assigned_to_id = $request->user_id;
            $outgoingGood->assigned_at = Carbon::now();
            $outgoingGood->is_assigned = true;
            $outgoingGood->status = 'in_progress';
            $outgoingGood->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Items successfully assigned'
        ]);
    }

    /**
     * Update the status of an outgoing good
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,ready,completed,cancelled'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $outgoingGood = OutgoingGood::findOrFail($id);
        $outgoingGood->status = $request->status;

        // Handle completion
        if ($request->status === 'completed' && !$outgoingGood->is_completed) {
            $outgoingGood->is_completed = true;
            $outgoingGood->completed_at = Carbon::now();
            $outgoingGood->completed_by = auth()->user()->npk;

            if ($request->has('completion_notes')) {
                $outgoingGood->completion_notes = $request->completion_notes;
            }
        }

        $outgoingGood->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => $outgoingGood
        ]);
    }

    /**
     * Generate a receipt for a completed outgoing good
     */
    public function generateReceipt($id)
    {
        $outgoingGood = OutgoingGood::with(['items', 'assignedTo', 'completedBy'])->findOrFail($id);

        // Check if completed
        if (!$outgoingGood->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot generate receipt for incomplete request'
            ], 400);
        }

        // Logic to generate PDF receipt would go here
        // For example, using a package like barryvdh/laravel-dompdf

        // This is just a placeholder - implement actual PDF generation
        $pdf = "PDF receipt content";

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="receipt-' . $outgoingGood->number . '.pdf"',
        ]);
    }

    /**
     * Get templates for outgoing goods
     */
    // public function getTemplates()
    // {
    //     $templates = Template::where('type', 'outgoing_good')
    //                         ->where('created_by', auth()->user()->npk)
    //                         ->get();

    //     return response()->json([
    //         'success' => true,
    //         'data' => $templates
    //     ]);
    // }

    // /**
    //  * Store a new template
    //  */
    // public function storeTemplate(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'items' => 'required|array|min:1',
    //         'priority' => 'required|in:low,normal,high,urgent',
    //         'notes' => 'nullable|string'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Validation error',
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     $template = new Template();
    //     $template->name = $request->name;
    //     $template->type = 'outgoing_good';
    //     $template->data = json_encode([
    //         'items' => $request->items,
    //         'priority' => $request->priority,
    //         'notes' => $request->notes
    //     ]);
    //     $template->created_by = auth()->user()->npk;
    //     $template->save();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Template saved successfully',
    //         'data' => $template
    //     ], 201);
    // }

    // /**
    //  * Delete a template
    //  */
    // public function deleteTemplate($id)
    // {
    //     $template = Template::where('id', $id)
    //                         ->where('created_by', auth()->user()->npk)
    //                         ->firstOrFail();

    //     $template->delete();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Template deleted successfully'
    //     ]);
    // }
}
