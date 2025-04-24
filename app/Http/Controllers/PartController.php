<?php

namespace App\Http\Controllers;

use App\Exports\PartsExport;
use App\Helpers\WhatsappHelper;
use App\Imports\PartsImport;
use App\Part;
use App\PartMonitoringSetting;
use App\PartStock;
use App\PartStockLog;
use Barryvdh\DomPDF\Facade as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PartController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15); // Default to 15 items per page if not specified
            $query = Part::query();

            $query = $query->with('partStock');

            if ($request->has('code') && $request->code != '') {
                $query->where('code', 'like', '%' . $request->code . '%');
            }

            if ($request->has('name') && $request->name != '') {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->has('category_code') && $request->category_code != '') {
                $query->where('category_code', $request->category_code);
            }

            if ($request->has('rack') && $request->rack != '') {
                $query->where('rack', $request->rack);
            }

            if ($request->has('status_stock') && in_array($request->status_stock, ['low', 'normal'])) {
                $query->whereHas('partStock', function ($query) use ($request) {
                    $query->whereHas('part', function ($query) use ($request) {
                        if ($request->status_stock == 'low') {
                            // MongoDB query to check if stock is less than min_stock (low stock)
                            $query->where('stock', '<', '$min_stock');
                        } else {
                            // MongoDB query to check if stock is greater than or equal to min_stock (normal stock)
                            $query->where('stock', '>=', '$min_stock');
                        }
                    });
                });
            }

            // if ($request->has('search')) {
            //     $searchTerm = $request->search;
            //     $query->where(function ($query) use ($searchTerm) {
            //         $query->where('code', 'like', '%' . $searchTerm . '%')
            //             ->orWhere('name', 'like', '%' . $searchTerm . '%');
            //     });
            // }

            $parts = $query->paginate($perPage);

            return response()->json([
                'message' => 'success',
                'data' => $parts
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function getPartList()
    {
        try {
            $parts = Part::select('id', 'code', 'name', 'category_code', 'is_partially_out')->get();
            return response()->json($parts);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'code' => 'nullable|string|max:255|unique:parts,code',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'stock' => 'nullable|min:0',
                'rack' => 'nullable|string|max:100',
                'brand_code' => 'nullable|string|max:155',
                'brand_name' => 'nullable|string|max:155',
                'min_stock' => 'nullable|min:0',
                'max_stock' => 'nullable|min:0',
                'uom' => 'nullable|string|max:255',
                'category_code' => 'nullable|string|max:255',
            ]);

            $codepart = '';
            if ($request->code != null || $request->code != '') {
                $codepart = $request->code;
            } else {
                $codepart = Part::generateNewCode();
            }

            $part = new Part([
                'code' => $codepart,
                'name' => $request->name,
                'description' => $request->description,
                'category_code' => $request->category_code,
                'min_stock' => floatval($request->min_stock) ?? 0,
                'max_stock' => floatval($request->max_stock) ?? 0,
                'rack' => $request->rack ?? null,
                'uom' => $request->uom ?? '',
                'brand_code' => $request->brand_code ?? null,
                'brand_name' => $request->brand_name ?? null,
                'is_partially_out' => $request->is_partially_out ?? false,
                'is_out_target' => $request->is_out_target ?? false,
                'created_by' => auth()->user()->npk,
                'last_updated_by' => auth()->user()->npk,
            ]);

            $part->save();

            PartStock::updateOrCreate(
                ['part_code' => $part->code],
                [
                    'stock' => $request->stock ?? 0,
                    'created_by' => auth()->user()->npk,
                    'updated_by' => auth()->user()->npk,
                ]
            );

            $part->generateQRCode();

            return response()->json([
                'message' => 'Part created successfully',
                'data' => $part
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function show($code)
    {
        try {
            $part = Part::where('code', $code)->first();

            if (!$part) {
                return response()->json([
                    'message' => 'Part not found',
                    'data' => null
                ], 404);
            }

            return response()->json(
                [
                    'message' => 'success',
                    'data' => $part
                ]
            );
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'data' => null,
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'category_code' => 'nullable|string|max:255',
                'min_stock' => 'nullable|integer|min:0',
                'max_stock' => 'nullable|integer|min:0',
                'rack' => 'nullable|string|max:100',
                'brand_code' => 'nullable|string|max:155',
                'brand_name' => 'nullable|string|max:155',
                'uom' => 'nullable|string|max:255',
            ]);

            $part = Part::findOrFail($id);
            $part->update([
                'name' => $request->name,
                'description' => $request->description ?? $part->description, // Use existing value if not provided
                'category_code' => $request->category_code ?? $part->category_code,
                'min_stock' => $request->min_stock ?? $part->min_stock,
                'max_stock' => $request->max_stock ?? $part->max_stock,
                'rack' => $request->rack ?? $part->rack,
                'brand_code' => $request->brand_code ?? $part->brand_code,
                'brand_name' => $request->brand_name ?? $part->brand_name,
                'uom' => $request->uom ?? $part->uom,
                'is_partially_out' => $request->is_partially_out ?? $part->is_partially_out,
                'is_out_target' => $request->is_out_target ?? $part->is_out_target,
                'last_updated_by' => auth()->user()->npk,
            ]);

            return response()->json([
                'message' => 'Part updated successfully',
                'data' => $part
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function updateStock(Request $request, $id)
    {
        try {
            $request->validate([
                'stock' => 'required|integer|min:0',
            ]);

            $userNpk = auth()->user()->npk;

            $part = Part::findOrFail($id);
            $newStock = (int) $request->stock;

            $oldStock = $part->partStock ? $part->partStock->stock : 0;
            $stockChange = $newStock - $oldStock;

            if ($part->partStock) {
                $part->partStock->stock = $request->stock;
            } else {
                $part->partStock()->create([
                    'stock' => $request->stock,
                    'created_by' => auth()->user()->npk,
                ]);
                $part->partStock->created_by = auth()->user()->npk;
            }

            $part->partStock->updated_by = auth()->user()->npk;
            $part->partStock->save();
            $part->last_updated_by = auth()->user()->npk;
            $part->save();

            PartStockLog::create([
                'part_code' => $part->code,
                'stock_change' => $stockChange,
                'new_stock' => $newStock,
                'action' => 'manual_update',
                'created_by' => $userNpk,
            ]);

            $setting = PartMonitoringSetting::first();

            $minStock = is_numeric($part->min_stock) ? (int) $part->min_stock : null;
            $maxStock = is_numeric($part->max_stock) ? (int) $part->max_stock : null;
            $message = null;

            if ($newStock <= 0) {
                $message = "⚠️ Part {$part->code} - {$part->name} is *Out of Stock*! Current stock: {$newStock} {$part->uom}.";
            } elseif (!is_null($minStock) && $newStock < $minStock) {
                $message = "🔻 Part {$part->code} - {$part->name} is *Low Stock*! Current stock: {$newStock} {$part->uom}. Min required: {$minStock} {$part->uom}.";
            } elseif (!is_null($maxStock) && $newStock > $maxStock) {
                $message = "🔺 Part {$part->code} - {$part->name} is *Over Stock*! Current stock: {$newStock} {$part->uom}. Max allowed: {$maxStock} {$part->uom}.";
            }

            if ($setting && $setting->enable_whatsapp && $setting->whatsapp_numbers) {
                $numbers = explode(',', $setting->whatsapp_numbers);
                WhatsappHelper::sendMessage($numbers, $message);
            }

            return response()->json([
                'message' => 'Part stock updated successfully',
                'data' => $part
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $part = Part::findOrFail($id);
            $part->delete();

            return response()->json([
                'message' => 'Part deleted successfully'
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
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls'
            ]);

            $file = $request->file('file');
            $Excels = Excel::toArray(new PartsImport, $file);

            $result = [];

            foreach ($Excels[0] as $row) {
                $row['can_parsially_out'] = isset($row['can_parsially_out']) && $row['can_parsially_out'] == 'Y' ? true : false;
                $row['must_select_out_target'] = isset($row['must_select_out_target']) && $row['must_select_out_target'] == 'Y' ? true : false;

                $partValOld = Part::where('code', $row['code'])->first();

                $part = Part::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'name' => !empty($row['name']) ? $row['name'] : $partValOld->name,
                        'description' => !empty($row['description']) ? $row['description'] : $partValOld->description,
                        'uom' => !empty($row['uom']) ? $row['uom'] : $partValOld->uom,
                        'min_stock' => !empty($row['min_stock']) ? $row['min_stock'] : $partValOld->min_stock,
                        'max_stock' => !empty($row['max_stock']) ? $row['max_stock'] : $partValOld->max_stock,
                        'rack' => !empty($row['rack']) ? $row['rack'] : $partValOld->rack,
                        'brand_name' => !empty($row['brand_name']) ? $row['brand_name'] : $partValOld->brand_name,
                        // 'brand_code' => !empty($row['brand_code']) ? $row['brand_code'] : $partValOld->brand_code,
                        'is_partially_out' => !empty($row['can_parsially_out']) ? $row['can_parsially_out'] ?? false : $partValOld->is_partially_out,
                        // 'is_out_target' => $row['must_select_out_target'] ?? false,
                        'updated_by' => auth()->user()->npk,
                    ]
                );

                // change stock if has part stock
                if ($row['stock']) {
                    if ($part->partStock) {
                        $part->partStock->update([
                            'stock' => $row['stock'],
                            'updated_by' => auth()->user()->npk,
                        ]);
                    } else {
                        $part->partStock()->create([
                            'stock' => $row['stock'],
                            'created_by' => auth()->user()->npk,
                            'updated_by' => auth()->user()->npk,
                        ]);
                    }
                }
                // $part->generateQRCode();
            }

            return response()->json([
                'data' => $result,
                'message' => 'Parts imported successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $selectedParts = $request->selectedParts;

        // If selectedParts is 0, export all parts
        $partsQuery = Part::query();

        if ($selectedParts && $selectedParts !== 0) {
            $partsQuery->whereIn('_id', $selectedParts);
        }

        // Get the selected or all parts
        $parts = $partsQuery->get();

        // Generate the Excel file and return it as a download directly
        return Excel::download(new PartsExport($parts), 'parts_export.xlsx');
    }

    public function printPdf(Request $request)
    {
        ini_set('memory_limit', '1024M');
        try {
            $selectedParts = $request->input('selectedParts');

            // If selectedParts is 0, we fetch all parts; otherwise, we fetch only the selected ones
            if ($selectedParts == 0) {
                $parts = Part::with('partStock')->get(); // Get all parts with their stock information
            } else {
                // Fetch the selected parts using the codes in selectedParts array
                $parts = Part::with('partStock')
                    ->whereIn('_id', $selectedParts)
                    ->get();
            }

            // If no parts were found, return a 404 response
            if ($parts->isEmpty()) {
                return response()->json(['message' => 'No parts found to print.'], 404);
            }

            $currentDate = Carbon::now()->toFormattedDateString(); // Get current date in a readable format
            $currentUser = auth()->user() ? auth()->user()->full_name : 'Guest'; // Get the current user's name

            // Generate the PDF
            $pdf = PDF::loadView('parts.print', compact('parts', 'currentDate', 'currentUser'));

            // Return the PDF as a downloadable response
            return $pdf->stream('parts_report.pdf');
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
