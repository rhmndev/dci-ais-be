<?php

namespace App\Http\Controllers;

use App\WhsScheduleDelivery;
use Illuminate\Http\Request;

class WhsScheduleDeliveryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $lists = WhsScheduleDelivery::query();

            if ($request->has('part_type') && $request->part_type != '') {
                $lists->where('part_type', $request->part_type);
            }

            $filterableFields = [
                'start_date_schedule',
                'end_date_schedule',
                'customer_name',
                'customer_plant',
                'po_no',
                'delivery',
                'description',
                'del_qty',
                'currency',
                'part_name',
                'part_number',
                'plant',
                'material',
                'dn_customer',
                'sloc',
                'part_type',
            ];

            foreach ($filterableFields as $field) {
                $value = $request->get($field);

                if (!is_null($value) && $value !== '') {
                    // Special case for date range
                    if ($field === 'start_date_schedule') {
                        $lists->where('ac_gi_date', '>=', $value);
                    } elseif ($field === 'end_date_schedule') {
                        $lists->where('ac_gi_date', '<=', $value);
                    } else {
                        $lists->where($field, 'like', '%' . $value . '%'); // for partial match
                    }
                }
            }
            // with CreatedUserBy
            $lists->with(['createdUserBy']);
            $orderBy = $request->get('orderBy', 'created_at'); // default ke created_at
            $orderDir = $request->get('orderDir', 'desc');     // default ke desc

            // Validasi orderDir hanya asc atau desc
            $orderDir = in_array(strtolower($orderDir), ['asc', 'desc']) ? $orderDir : 'desc';

            $lists->orderBy($orderBy, $orderDir);

            // Pagination
            $perPage = (int) $request->get('per_page', 10);
            $page = (int) $request->get('page', 1);

            $perPage = $perPage > 0 ? $perPage : 10;
            $page = $page > 0 ? $page : 1;

            $data = $lists->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'type' => 'success',
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function createCustomerDeliveryCycle(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            // 'part_id' => 'required',
            'schedule_date' => 'required',
            // 'po_no' => 'required',
            // 'delivery' => 'required',
            'ac_gi_date' => 'required',
        ]);

        try {
            $data = $request->all();
            $data['created_by'] = auth()->user()->npk;
            $data['updated_by'] = auth()->user()->npk;

            $whsScheduleDelivery = WhsScheduleDelivery::updateOrCreate(
                [
                    'ac_gi_date' => $request->ac_gi_date,
                    'customer_id' => $request->customer_id,
                    // 'part_id' => $request->part_id,
                    'schedule_date' => $request->schedule_date,
                    'po_no' => $request->po_no,
                    'cycle' => $request->cycle,
                ],
                [
                    'customer_name' => $request->customer_name,
                    'customer_plant' => $request->customer_plant,
                    'part_id' => $request->part_id,
                    'delivery' => $request->delivery,
                    'part_name' => $request->part_name,
                    'part_number' => $request->part_number,
                    'part_type' => $request->part_type,
                    'ac_gi_date' => $request->ac_gi_date,
                    'cycle' => $request->cycle,
                    'schedule_date' => $request->schedule_date,
                    'quantity' => $request->quantity,
                    'show' => $request->show,
                    'qty' => $request->qty,
                    'status_prd' => $request->status_prd,
                    'status_qc' => $request->status_qc,
                    'status_spa' => $request->status_spa,
                    'status_ok' => $request->status_ok,
                    'created_by' => $data['created_by'],
                    'updated_by' => $data['updated_by'],
                ]
            );

            return response()->json([
                'type' => 'success',
                'message' => 'Data has been created',
                'data' => $whsScheduleDelivery
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function updateCustomerDeliveryCycle(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'required',
            // 'part_id' => 'required',
            'schedule_date' => 'required',
            // 'po_no' => 'required',
            // 'delivery' => 'required',
            'ac_gi_date' => 'required',
        ]);

        try {
            $whsScheduleDelivery = WhsScheduleDelivery::findOrFail($id);
            $whsScheduleDelivery->update($request->all());

            return response()->json([
                'type' => 'success',
                'message' => 'Data has been updated',
                'data' => $whsScheduleDelivery
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function destroyCustomerScheduleDeliveryCycle(Request $request, $id)
    {
        try {
            $whsScheduleDelivery = WhsScheduleDelivery::findOrFail($id);
            $whsScheduleDelivery->qty = null;
            $whsScheduleDelivery->cycle = null;
            $whsScheduleDelivery->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Data has been deleted',
                'data' => $whsScheduleDelivery
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }
}
