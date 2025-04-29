<?php

namespace App\Http\Controllers;

use App\Material;
use App\OutgoingGood;
use App\OutgoingGoodItem;
use App\OutgoingGoodTemplate;
use App\OutgoingGoodTemplateItem;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class OutgoingGoodController extends Controller
{
    /**
     * Display a listing of outgoing goods
     */
    public function index(Request $request)
    {
        $query = OutgoingGood::with(['items', 'assignedTo']);

        // Filter by assignment status
        // if ($request->has('is_assigned')) {
        //     $query->where('is_assigned', $request->is_assigned);
        // }

        // Filter by completion status
        if ($request->has('is_completed')) {
            $query->where('is_completed', $request->is_completed);
        }

        // Filter by status
        // if ($request->has('status') && $request->status !== 'all') {
        //     $query->where('status', $request->status);
        // }

        $perPage = $request->input('per_page', 10); // default 10 items per page
        $page = $request->input('page', 1); // default to page 1

        $outgoingGoods = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'success' => true,
            'data' => $outgoingGoods->items(),
            'current_page' => $outgoingGoods->currentPage(),
            'last_page' => $outgoingGoods->lastPage(),
            'per_page' => $outgoingGoods->perPage(),
            'total' => $outgoingGoods->total(),
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
        $outgoingGood->handle_for_type = $request->handle_for_type ?? 'internal'; // Default to 'internal' if not provided
        $outgoingGood->handle_for_id = $request->handle_for_id ?? null; // Default to null if not provided
        $outgoingGood->status = 'pending';
        $outgoingGood->is_assigned = ($request->handle_for) ? true : false;
        $outgoingGood->is_completed = false;
        $outgoingGood->created_by = auth()->user()->npk;
        $outgoingGood->notes = $request->notes;

        $outgoingGood->assigned_to = $request->handle_for_id;
        // === QR Code Generation ===
        $qrPath = 'whs/bkb/qr/' . $refNumber . '.png';
        $qrCode = QrCode::format('png')->size(300)->generate($refNumber);
        Storage::disk('public')->put($qrPath, $qrCode);
        $outgoingGood->qr_code_path = $qrPath;
        $outgoingGood->take_material_from_location = $request->take_material_from_location ?? null; // Default to null if not provided

        $outgoingGood->save();

        // Save items
        foreach ($request->items as $item) {
            $material = Material::where('code', $item['material_code'])->first();

            $outgoingItem = new OutgoingGoodItem();
            $outgoingItem->outgoing_good_id = $outgoingGood->id;
            $outgoingItem->outgoing_good_number = $refNumber;
            $outgoingItem->created_by = auth()->user()->npk;
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
            'data' => $outgoingGood->load('items'),

            'qr_code_url' => asset('storage/' . $qrPath)
        ], 201);
    }

    /**
     * Display the specified outgoing good
     */
    public function show($id)
    {
        $outgoingGood = OutgoingGood::with(['items', 'assignedTo'])->findOrFail($id);

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
            'outgoing_ids.*' => 'required|exists:outgoing_goods,_id',
            'user_id' => 'required|exists:users,_id',
            'user_type' => 'required',
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
        if (
            ($request->user_type === 'internal' && !in_array($user->type, [0])) ||
            ($request->user_type === 'external' && $user->type !== 1)
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
    public function getTemplates()
    {
        $templates = OutgoingGoodTemplate::with('items')->get();

        return response()->json([
            'success' => true,
            'data' => $templates
        ]);
    }

    // /**
    //  * Store a new template
    //  */
    public function storeOrUpdateTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code_template' => 'nullable|string',
            'name_template' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $isUpdate = !empty($request->code_template);
        $userNpk = auth()->user()->npk;

        if ($isUpdate) {
            $template = OutgoingGoodTemplate::where('code_template', $request->code_template)->first();

            if (!$template) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template not found'
                ], 404);
            }

            $template->updated_by = $userNpk;
        } else {
            $template = new OutgoingGoodTemplate();
            $template->code_template = 'OGT-' . date('Ymd') . '-' . Str::random(6);
            $template->created_by = $userNpk;
        }

        $template->name_template = $request->name_template;
        $template->notes = $request->notes;
        $template->save();

        // Clear old items if updating
        if ($isUpdate) {
            OutgoingGoodTemplateItem::where('code_template', $template->code_template)->delete();
        }

        foreach ($request->items as $item) {
            $material = Material::where('code', $item['material_code'])->first();

            if (!$material) {
                continue; // or handle as error
            }

            $templateItem = new OutgoingGoodTemplateItem();
            $templateItem->code_template = $template->code_template;
            $templateItem->created_by = $userNpk;
            $templateItem->material_code = $item['material_code'];
            $templateItem->material_name = $material->description;
            $templateItem->quantity_needed = $item['quantity_needed'];
            $templateItem->uom_needed = $material->unit;
            $templateItem->save();
        }

        return response()->json([
            'success' => true,
            'message' => $isUpdate ? 'Template updated successfully' : 'Template created successfully',
            'data' => $template
        ], $isUpdate ? 200 : 201);
    }

    // /**
    //  * Delete a template
    //  */
    public function deleteTemplate($id)
    {
        $template = OutgoingGoodTemplate::findOrFail($id);

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully'
        ]);
    }
}
