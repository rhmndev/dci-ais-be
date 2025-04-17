<?php

namespace App\Http\Controllers;

use App\Imports\OrderCustomerImport;
use App\OrderCustomer;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OrderCustomerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = OrderCustomer::query();

            if ($request->has('dn_no')) {
                $query->where('dn_no', 'like', '%' . $request->dn_no . '%');
            }

            if ($request->has('part_no')) {
                $query->where('part_no', 'like', '%' . $request->part_no . '%');
            }

            if ($request->has('search') && $request->search != '') {
                $searchTerm = $request->search;
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('dn_no', 'like', '%' . $searchTerm . '%')
                        ->orWhere('part_no', 'like', '%' . $searchTerm . '%');
                });
            }

            // Return as simple collection
            $orders = $query->get([
                'customer',
                'plant',
                'dn_no',
                'part_no',
                'part_name',
                'job_no',
                'del_date',
                'del_time',
                'cycle',
                'qty',
                'qty_kbn',
                'last_upd',
                'user_id'
            ]);

            return response()->json([
                'message' => 'success',
                'data' => $orders
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer' => 'required|string|max:300',
            'plant' => 'required|string|max:50',
            'dn_no' => 'required|string|max:150',
            'part_no' => 'required|string|max:300',
            'part_name' => 'required|string|max:765',
            'job_no' => 'required|string|max:150',
            'del_date' => 'required|date',
            'del_time' => 'required|date_format:H:i:s',
            'cycle' => 'required|string|max:15',
            'qty' => 'required|integer',
            'qty_kbn' => 'required|integer'
        ]);

        try {
            $order = OrderCustomer::create($request->all());
            return response()->json([
                'message' => 'Order created successfully',
                'data' => $order
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }


    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
            'customer' => 'required|string|max:300',
        ]);
        try {
            $file = $request->file('file');
            $Excels = Excel::toArray(new OrderCustomerImport, $file);
            $success = [];
            $failed = [];

            foreach ($Excels[0] as $index => $row) {
                try {
                    OrderCustomer::create([
                        'customer' => $row['customer'],
                        'dn_no' => $row['order_no'],
                        'plant' => $row['plant_code'],
                        'part_no' => $row['part_no'],
                        'part_name' => $row['part_name'],
                        'job_no' => $row['job_no'],
                        'del_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['del_date'])->format('Y-m-d'),
                        'del_time' => $row['del_time'],
                        'cycle' => $row['del_cycle'],
                        'qty' => $row['qtykbn'],
                        'qty_kbn' => $row['orderkbn'],
                        'last_upd' => now(),
                        'user_id' => auth()->user()->npk,
                    ]);

                    $success[] = [
                        'row' => $index + 1,
                        'dn_no' => $row['order_no'],
                        'row' => $row
                    ];
                } catch (\Exception $e) {
                    $failed[] = [
                        'row' => $index + 1,
                        'dn_no' => $row['dn_no'] ?? null,
                        'error' => $e->getMessage()
                    ];
                }
            }

            return response()->json([
                'message' => 'Import completed',
                'success_count' => count($success),
                'failed_count' => count($failed),
                'success_data' => $success,
                'failed_data' => $failed
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
