<?php

namespace App\Http\Controllers;

use App\CustomerScheduleDeliveryCycle;
use App\CustomerScheduleDeliveryList;
use App\CustomerScheduleDeliveryPickupTime;
use App\Http\Resources\CustomerScheduleDeliveryListResources;
use App\Imports\DeliveriesImport;
use App\Imports\CustomerDeliveriesImport;
use App\Imports\CustomerScheduleDeliveryCycleImport;
use App\Imports\CustomerScheduleDeliveryListImport;
use App\Imports\ScheduleDeliveriesImport;
use App\OrderCustomer;
use App\WhsScheduleDelivery;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CustomerScheduleDeliveryListController extends Controller
{
    public function index(Request $request)
    {
        $lists = CustomerScheduleDeliveryList::query();
        $lists->select('customer_id', 'customer_name', 'customer_plant', 'customer_alias', 'customer_image', 'part_type', 'show');

        if ($request->has('part_type')) {
            $lists->where('part_type', $request->part_type);
        }

        if ($request->has('show')) {
            $lists->where('show', $request->show);
        } else {
            $lists->where('show', true);
        }

        $lists->groupBy('customer_name', 'customer_id', 'customer_alias', 'customer_plant');

        if ($request->has('group_by')) {
            $lists->groupBy($request->group_by);
        }
        $lists->orderBy('customer_name', 'asc');

        $lists = $lists->get();

        $lists = $lists->map(function ($list) {
            $cycles = $list->getCycles();
            if ($cycles->isNotEmpty()) {
                $list->cycles = $cycles->pluck('cycle');
            } else {
                $list->cycles = [];
            }
            return $list;
        });

        $lists = $lists->map(function ($list) {
            $pickUpTimes = $list->getPickUpTimes();
            if ($pickUpTimes->isNotEmpty()) {
                $list->pick_up_times = $pickUpTimes;
            } else {
                $list->pick_up_times = [];
            }
            return $list;
        });

        $lists = $lists->map(function ($list) use ($request) {
            $parts = $list->getListParts($request->part_type);
            if ($parts->isNotEmpty()) {
                $list->parts = $parts;
            } else {
                $list->parts = [];
            }
            return $list;
        });

        $lists = $lists->map(function ($list) {
            $schedule_parts = $list->getScheduleParts();
            if ($schedule_parts->isNotEmpty()) {
                $list->schedule_parts = $schedule_parts;
            } else {
                $list->schedule_parts = [];
            }
            return $list;
        });

        $lists = $lists->map(function ($list) {
            $status_parts = $list->getStatusParts();
            if ($status_parts->isNotEmpty()) {
                $list->status_parts = $status_parts;
            } else {
                $list->status_parts = [];
            }
            return $list;
        });

        return response()->json([
            'type' => 'success',
            'data' => CustomerScheduleDeliveryListResources::collection($lists),
            'total' => $lists->count(),
        ], 200);
    }

    public function createList(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|string',
            'customer_name' => 'required|string',
            'customer_plant' => 'required|string',
            'customer_alias' => 'required|string',
            'customer_image' => 'string',
            'part_no' => 'required|string',
            'part_name' => 'required|string',
            'part_type' => 'required|string',
            // 'show' => 'required|boolean',
        ]);
        try {
            $list = CustomerScheduleDeliveryList::create($request->all());
            return response()->json([
                'message' => 'List created successfully',
                'data' => $list,
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Failed to create list',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    public function getDeliverySchedules(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // Query OrderCustomer with pagination
        $CustomerScheduleDeliveryList = CustomerScheduleDeliveryList::query();

        $filterableFields = [
            'customer_id',
            'customer_name',
            'customer_plant',
            'part_no',
            'part_name',
            'part_type',
        ];
        foreach ($filterableFields as $field) {
            $value = $request->get($field);
            if (!is_null($value) && $value !== '') {
                if ($field === 'customer_id') {
                    // Handle both string and number safely
                    $CustomerScheduleDeliveryList->where($field, 'like', '%' . strval($value) . '%');
                } else {
                    $CustomerScheduleDeliveryList->where($field, 'like', '%' . $value . '%');
                }
            }
        }

        $CustomerScheduleDeliveryList = $CustomerScheduleDeliveryList->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'type' => 'success',
            'data' => $CustomerScheduleDeliveryList,
            'total' => $CustomerScheduleDeliveryList->total(),
        ], 200);
    }

    public function importScheduleDeliveries(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $data = Excel::toArray(new ScheduleDeliveriesImport, $request->file('file'));

            $deliveries = [];
            foreach ($data[0] as $row) {
                $dataDelivery = WhsScheduleDelivery::updateOrCreate(
                    [
                        'customer_id' => $row['ship_to'] ?? null,
                        'part_id' => $row['material'] ?? null,
                        'schedule_date' => isset($row['ac_gi_date']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['ac_gi_date'])->format('Y-m-d') : null,
                        'po_no' => $row['purchase_order_no'],
                        'delivery' => $row['delivery'] ?? null,
                    ],
                    [
                        'customer_name' => $row['customer'] ?? null,
                        'customer_plant' => $row['plant'] ?? null,
                        'part_type' => $row['shpt'] ?? null,
                        'schedule_date' => isset($row['ac_gi_date']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['ac_gi_date'])->format('Y-m-d') : null,
                        'time' => $row['time'] ?? null,
                        'part_id' => $row['material'] ?? null,
                        'part_number' => $row['customer_material_number'] ?? null,
                        'part_name' => $row['description'] ?? null,
                        'cycle' => $row['cycle'] ?? null,
                        'slock' => $row['slock'] ?? null,
                        'qty' => $row['delivery_quantity'] ?? null,
                        'planning_time' => $row['planning_time'] ?? null,
                        'on_time' => $row['on_time'] ?? null,
                        'delay' => $row['delay'] ?? null,
                        'status_prod' => $row['status_prod'] ?? null,
                        'status_qc' => $row['status_qc'] ?? null,
                        'status_spa' => $row['status_spa'] ?? null,
                        'status_ok' => $row['status_ok'] ?? null,
                        'status_ready_to_delivery' => $row['status_ready_to_delivery'] ?? null,
                        'status_delivery' => $row['status_delivery'] ?? null,
                        'po_no' => $row['purchase_order_no'] ?? null,
                        'po_date' => isset($row['po_date']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['po_date'])->format('Y-m-d') : null,
                        'ext_delivery_id' => $row['external_delivery_id'] ?? null,
                        'delivery' => $row['delivery'] ?? null,
                        'dn_customer' => isset($row['dn_customer']) ? $row['dn_customer'] : null,
                        'customer_mat_no' => $row['customer_mat_no'] ?? null,
                        'description' => $row['description'] ?? null,
                        'item' => $row['item'] ?? null,
                        'del_qty' => $row['delivery_quantity'] ?? null,
                        'net_price' => $row['net_price'] ?? null,
                        'total' => $row['total'] ?? null,
                        'pod_status' => $row['pod_status'] ?? null,
                        'gm' => $row['gm'] ?? null,
                        'bs' => $row['bs'] ?? null,
                        'currency' => $row['curr'] ?? null,
                        'su' => $row['su'] ?? null,
                        'material' => $row['material'] ?? null,
                        'shpt' => $row['shpt'] ?? null,
                        'ac_gi_date' => isset($row['ac_gi_date']) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['ac_gi_date'])->format('Y-m-d') : null,
                        'time' => $row['time'] ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['time'])->format('H:i') : null,
                        'dlvt' => $row['dlvt'] ?? null,
                        'ship_to' => $row['ship_to'] ?? null,
                        'name_ship_to' => $row['name_ship_to'] ?? null,
                        'ref_doc' => $row['ref_doc'] ?? null,
                        'dchl' => $row['dchl'] ?? null,
                        'sloc' => $row['sloc'] ?? null,
                        'sorg' => $row['sorg'] ?? null,
                        'quantity_dn' => $row['quantity_dn'] ?? null,
                        'status_dn' => $row['status_dn'] ?? null,
                        'created_by' => $row['created_by'] ?? auth()->user()->npk,
                    ]
                );

                $deliveries[] = $dataDelivery;
            }

            return response()->json([
                'message' => 'Deliveries imported successfully',
                'data' => $deliveries,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to import deliveries',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyCustomerScheduleDeliveryList($id)
    {
        try {
            $list = CustomerScheduleDeliveryList::findOrFail($id);
            $list->delete();

            return response()->json([
                'message' => 'List deleted successfully',
                'data' => $list,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete list',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function createCustomerCycle(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'customer_name' => 'required|string',
            'customer_plant' => 'required|string',
            'cycle' => 'required|string',
            'part_type' => 'required|string',
        ]);

        try {
            $customer_id = (int) $request->customer_id;
            $dataCustomer = CustomerScheduleDeliveryCycle::updateOrCreate(
                [
                    'customer_id' => $request->customer_id,
                    'customer_plant' =>  $request->customer_plant,
                    'cycle' => $request->cycle,
                    'part_type' => $request->part_type,
                ],
                [
                    'customer_name' => $request->customer_name,
                    'customer_plant' => $request->customer_plant,
                    'customer_alias' => $request->customer_alias ?? $request->customer_plant,
                    'part_type' => $request->part_type,
                    'cycle' => $request->cycle,
                    'customer_id' => $customer_id,
                ]
            );

            return response()->json([
                'type' => 'success',
                'message' => 'Pickup time created successfully',
                'data' => $dataCustomer,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function createCustomerPickupTime(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'customer_name' => 'required|string',
            'customer_plant' => 'required|string',
            'pickup_time' => 'required|string',
            'type' => 'required|string',
            'part_type' => 'required|string',
        ]);

        try {
            $customer_id = (int) $request->customer_id;
            $dataCustomer = CustomerScheduleDeliveryPickupTime::updateOrCreate(
                [
                    'customer_id' => $customer_id,
                    'customer_plant' => $request->customer_plant,
                    'type' => $request->type,
                    'part_type' => $request->part_type,
                ],
                [
                    'customer_name' => $request->customer_name,
                    'customer_plant' => $request->customer_plant,
                    'customer_alias' => $request->customer_alias ?? $request->customer_plant,
                    'pickup_time' => $request->pickup_time,
                    'type' => $request->type,
                    'part_type' => $request->part_type,
                ]
            );

            return response()->json([
                'type' => 'success',
                'message' => 'Pickup time created successfully',
                'data' => $dataCustomer,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function updateCustomerPickupTime(Request $request, $id)
    {
        $request->validate([
            'customer_id' => 'required',
            'customer_name' => 'required|string',
            'customer_plant' => 'required|string',
            'pickup_time' => 'required|string',
            'type' => 'required|string',
            'part_type' => 'required|string',
        ]);

        try {
            $customer_id = (int) $request->customer_id;
            $dataCustomer = CustomerScheduleDeliveryPickupTime::findOrFail($id);
            $dataCustomer->update([
                'customer_id' => $customer_id,
                'customer_name' => $request->customer_name,
                'customer_plant' => $request->customer_plant,
                'customer_alias' => $request->customer_alias ?? $request->customer_plant,
                'pickup_time' => $request->pickup_time,
                'type' => $request->type,
                'part_type' => $request->part_type,
            ]);

            return response()->json([
                'type' => 'success',
                'message' => 'Pickup time updated successfully',
                'data' => $dataCustomer,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'type' => 'error',
                'message' => $th->getMessage(),
                'data' => []
            ], 500);
        }
    }

    public function destroyCustomerScheduleDeliveryPickupTime($id)
    {
        try {
            $time = CustomerScheduleDeliveryPickupTime::findOrFail($id);
            $time->delete();

            return response()->json([
                'message' => 'Pickup time deleted successfully',
                'data' => $time,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete pickup time',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroyCustomerScheduleDeliveryCycle($id)
    {
        try {
            $cycle = CustomerScheduleDeliveryCycle::findOrFail($id);
            $cycle->delete();

            return response()->json([
                'message' => 'Cycle deleted successfully',
                'data' => $cycle,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete cycle',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $delivery = WhsScheduleDelivery::findOrFail($id);
            $delivery->delete();

            return response()->json([
                'message' => 'Delivery deleted successfully',
                'data' => $delivery,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete delivery',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroySelected(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        try {
            $deliveries = WhsScheduleDelivery::whereIn('_id', $request->ids)->get();
            foreach ($deliveries as $delivery) {
                $delivery->delete();
            }

            return response()->json([
                'message' => 'Deliveries deleted successfully',
                'data' => $deliveries,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete deliveries',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function importCustomer(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $data = Excel::toArray(new CustomerScheduleDeliveryListImport, $request->file('file'));

            $customers = [];
            foreach ($data[0] as $index => $row) {
                $dataCustomer = CustomerScheduleDeliveryList::updateOrCreate(
                    [
                        'customer_id' => $row['customer_id'] ?? null,
                        'customer_name' => $row['customer_name'] ?? null,
                        'part_type' => $row['part_type'] ?? null,
                        'part_no' => $row['part_no'] ?? null,
                    ],
                    [
                        'customer_id' => $row['customer_id'] ?? null,
                        'customer_name' => $row['customer_name'] ?? null,
                        'customer_plant' => $row['customer_plant'] ?? null,
                        'customer_alias' => $row['customer_alias'] ?? null,
                        'customer_image' => $row['customer_image'] ?? null,
                        'part_type' => $row['part_type'] ?? null,
                        'part_no' => $row['part_no'] ?? null,
                        'part_name' => $row['part_name'] ?? null,
                        'show' => ($row['show'] ?? true) == 1 || strtolower($row['show']) == 'true',
                    ]
                );

                $customers[] = $dataCustomer;
            }

            return response()->json([
                'message' => 'Customers imported successfully',
                'data' => $customers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to import customers',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function importCustomerCycle(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $data = Excel::toArray(new CustomerDeliveriesImport, $request->file('file'));

            $customers = [];
            foreach ($data[0] as $index => $row) {
                if ($index == 0) {
                    continue;
                }
                $dataCustomer = CustomerScheduleDeliveryCycle::updateOrCreate(
                    [
                        'customer_id' => $row[0] ?? null,
                        'customer_plant' => $row[2] ?? null,
                        'cycle' => $row[4] ?? null,
                    ],
                    [
                        'customer_name' => $row[1] ?? null,
                        'customer_plant' => $row[2] ?? null,
                        'customer_alias' => $row[3] ?? null,
                        'cycle' => $row[4] ?? null,
                    ]
                );

                $customers[] = $dataCustomer;
            }

            return response()->json([
                'message' => 'Customers cycle imported successfully',
                'data' => $customers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to import customers',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function importCustomerPickupTime(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        try {
            $data = Excel::toArray(new CustomerDeliveriesImport, $request->file('file'));

            $customers = [];
            foreach ($data[0] as $index => $row) {
                if ($index == 0) {
                    continue;
                }

                $dataCustomer = CustomerScheduleDeliveryPickupTime::updateOrCreate(
                    [
                        'customer_id' => $row[0] ?? null,
                        'customer_plant' => $row[2] ?? null,
                        'type' => $row[5] ?? null,
                        'part_type' => $row[4] ?? null,
                    ],
                    [
                        'customer_name' => $row[1] ?? null,
                        'customer_plant' => $row[2] ?? null,
                        'customer_alias' => $row[3] ?? null,
                        'part_type' => $row[4] ?? null,
                        'type' => $row[5] ?? null,
                        'pickup_time' => $row[6] ?? null,
                    ]
                );

                $customers[] = $dataCustomer;
            }
            return response()->json([
                'message' => 'Customers cycle imported successfully',
                'data' => $customers,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to import customers',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getCustomerCycleList(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15); // Default to 15 items per page if not specified
            $page = $request->input('page', 1); // Default to page 1 if not specified

            $data = CustomerScheduleDeliveryCycle::query();

            if ($request->has('customer_id')) {
                $data->where('customer_id', $request->customer_id);
            }

            if ($request->has('part_type')) {
                $data->where('part_type', $request->part_type);
            }

            if ($request->has('cycle')) {
                $data->where('cycle', $request->cycle);
            }

            $cycles = $data->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'type' => 'success',
                'data' => $cycles->items(),
                'total' => $cycles->total(),
                'current_page' => $cycles->currentPage(),
                'last_page' => $cycles->lastPage(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve customer cycle list',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function getCustomerPickupTimeList(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15); // Default to 15 items per page if not specified
            $page = $request->input('page', 1); // Default to page 1 if not specified

            $data = CustomerScheduleDeliveryPickupTime::query();

            if ($request->has('customer_id')) {
                $data->where('customer_id', $request->customer_id);
            }

            if ($request->has('part_type')) {
                $data->where('part_type', $request->part_type);
            }

            $result = $data->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'type' => 'success',
                'data' => $result->items(),
                'total' => $result->total(),
                'current_page' => $result->currentPage(),
                'last_page' => $result->lastPage(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve customer cycle list',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteSelected(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        try {
            $deliveries = CustomerScheduleDeliveryList::whereIn('_id', $request->ids)->get();
            foreach ($deliveries as $delivery) {
                $delivery->delete();
            }

            return response()->json([
                'message' => 'Deliveries deleted successfully',
                'data' => $deliveries,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete deliveries',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
