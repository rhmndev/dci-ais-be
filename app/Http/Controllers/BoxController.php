<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Box;
use App\Customer;
use App\Imports\BoxesImport;
use App\TrackingBox;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class BoxController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Box::query();

            if ($request->has('search') && !is_null($request->input('search'))) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('number_box', 'like', '%' . $search . '%')
                        ->orWhere('type_box', 'like', '%' . $search . '%')
                        ->orWhere('status_box', 'like', '%' . $search . '%');
                });
            }

            if ($request->has('number_box')) {
                $query->where('number_box', 'like', '%' . $request->input('number_box') . '%');
            }

            if ($request->has('type_box') && !is_null($request->input('type_box'))) {
                $typeBox = $request->input('type_box');

                if (is_numeric($typeBox)) {
                    // If it's a number, use direct comparison
                    $query->where('type_box', $typeBox);
                } else {
                    // If it's a string, use LIKE for partial matching
                    $query->where('type_box', 'like', '%' . $typeBox . '%');
                }
            }

            if ($request->has('status_box')) {
                $query->where('status_box', 'like', '%' . $request->input('status_box') . '%');
            }

            $query->orderBy('number_box_alias', 'asc');

            $perPage = $request->input('per_page', 20);
            $boxes = $query->paginate($perPage);

            $boxes->getCollection()->transform(function ($box) {
                $box->last_status = $box->getLastStatusBox();
                return $box;
            });

            return response()->json($boxes, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve boxes'], 500);
        }
    }

    public function getBoxDetails(Request $request): JsonResponse
    {
        $request->validate([
            'number_box' => 'required',
            'type' => 'string',
        ]);
        try {
            $numberBox = $request->input('number_box');
            $type = $request->input('type');

            if (is_null($type) || $type !== 'in') {
                $latestTracking = TrackingBox::where('number_box', $numberBox)
                    ->orderBy('date_time', 'desc')
                    ->first();

                if ($latestTracking && in_array($latestTracking->status, ['out', 'delivery'])) {
                    return response()->json(['error' => 'Box is not valid for retrieval'], 400);
                }
            }

            $box = Box::where('qr_number', $numberBox)->first();

            if (!$box) {
                return response()->json(['error' => 'Box not found'], 404);
            }

            return response()->json([
                'message' => 'Successfully retrieved box details',
                'data' => $box,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve box details'], 500);
        }
    }

    public function getColorData(Request $request): JsonResponse
    {
        $request->validate([
            'code_color' => 'required|string',
        ]);

        $codeColor = $request->input('code_color');
        $colorData = Box::getColorData($codeColor);

        if ($colorData) {
            return response()->json($colorData, 200);
        } else {
            return response()->json(['error' => 'Color not found'], 404);
        }
    }

    public function getColorCodes(): JsonResponse
    {
        $colorCodes = Box::getColorCodes();
        return response()->json($colorCodes, 200);
    }

    public function getTypeBoxes(Request $request): JsonResponse
    {
        $typeBoxes = Box::getTypeBoxes();

        if ($request->has('type_box')) {
            $typeBox = $request->input('type_box');
            $typeBoxes = array_filter($typeBoxes, function ($box) use ($typeBox) {
                return $box['code_box'] == $typeBox;
            });
        }

        return response()->json($typeBoxes, 200);
    }

    public function countBoxesByType(Request $request): JsonResponse
    {
        try {
            $pipeline = [
                ['$group' => ['_id' => '$type_box', 'total' => ['$sum' => 1]]],
                ['$project' => ['type_box' => '$_id', 'total' => 1, '_id' => 0]]
            ];

            if ($request->has('type_box')) {
                $typeBox = $request->input('type_box');
                array_unshift($pipeline, [
                    '$match' => [
                        '$or' => [
                            ['type_box' => (int)$typeBox],
                            ['type_box' => (string)$typeBox]
                        ]
                    ]
                ]);
            }

            $boxCounts = Box::raw(function ($collection) use ($pipeline) {
                return $collection->aggregate($pipeline);
            });

            return response()->json([
                'message' => 'Successfully retrieved box counts by type',
                'data' => $boxCounts,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve box counts by type',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAnalyticDataBox(Request $request): JsonResponse
    {
        try {
            $total = 0;
            foreach (Customer::getCustomerList() as $key => $value) {
                $totalBoxOut[$value['code_name']] = 0;
            }
            $query = Box::query();

            if ($request->has('type_box')) {
                $query->where('type_box', 'like', '%' . $request->input('type_box') . '%');
            }

            if ($request->has('status_box')) {
                $query->where('status_box', 'like', '%' . $request->input('status_box') . '%');
            }

            $boxes = $query->get();
            $total = $boxes->count();

            foreach ($boxes as $box) {
                $latestTracking = TrackingBox::where('number_box', $box->number_box)
                    ->orderBy('date_time', 'desc')
                    ->first();

                if ($latestTracking && in_array($latestTracking->status, ['out', 'delivery'])) {
                    if (isset($totalBoxOut[$latestTracking->destination_code])) {
                        $totalBoxOut[$latestTracking->destination_code]++;
                    } else {
                        $totalBoxOut[$latestTracking->destination_code] = 1;
                    }
                }
            }

            $totalBoxInArea = $total - array_sum($totalBoxOut);

            foreach (Customer::getCustomerList() as $key => $value) {
                if (isset($totalBoxOut[$value['code_name']])) {
                    if (isset($totalOut['total_box_out_' . $value['customer']])) {
                        $totalOut['total_box_out_' . $value['customer']] += $totalBoxOut[$value['code_name']];
                    } else {
                        $totalOut['total_box_out_' . $value['customer']] = $totalBoxOut[$value['code_name']];
                    }
                } else {
                    $totalOut['total_box_out_' . $value['customer']] = 0;
                }
            }

            $result = [
                'total_box' => $total,
                'total_box_in_area' => $totalBoxInArea
            ];

            foreach (Customer::getCustomerList() as $key => $value) {
                $result['total_box_out_' . $value['customer']] = $totalOut['total_box_out_' . $value['customer']];
            }

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve analytic data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function getStatusChartData(Request $request): JsonResponse
    {
        try {
            $boxes = Box::all();
            $statusData = [
                'in' => 0,
                'out' => 0,
            ];

            foreach ($boxes as $box) {
                $lastStatus = $box->getLastStatusBox();
                if ($lastStatus) {
                    if ($lastStatus->status === 'in') {
                        $statusData['in']++;
                    } elseif ($lastStatus->status === 'out') {
                        $statusData['out']++;
                    }
                }
            }

            return response()->json([
                'message' => 'Successfully retrieved status chart data',
                'data' => $statusData,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve last status box'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'number_box' => 'required|string',
                'type_box' => 'required|string',
                'status_box' => 'required|string',
                'color_code_box' => 'required|string',
            ]);

            $plant = env('PLANT_CODE');
            $box = new Box($request->all());
            $box->plant = $plant;
            $box->number_box_alias = $request->number_box;
            $box->color_code_box = $box->color_code_box ?? Box::DEFAULTCOLOR;
            $box->number_box = $plant . '-' . $box->type_box . '-' . $box->color_code_box . '-' . $box->number_box;
            $box->qr_number = $box->number_box;
            $box->qr_code = $this->generateQrCode($box->qr_number);
            $box->save();

            return response()->json($box, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create box', 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $box = Box::findOrFail($id);
            return response()->json($box, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Box not found'], 404);
        }
    }
    public function showDetails(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'number_box' => 'required|string',
            ]);

            $box = Box::where('number_box', $request->input('number_box'))->first();

            $box->last_status = $box->getLastStatusBox();

            if (!$box) {
                return response()->json(['error' => 'Box not found'], 404);
            }

            return response()->json($box, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Box not found'], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            // $request->validate([
            //     'number_box' => 'string',
            //     'type_box' => 'integer',
            //     'status_box' => 'string',
            // ]);

            $box = Box::findOrFail($id);
            $box->update($request->all());

            return response()->json($box, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update box'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            Box::findOrFail($id)->delete();
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete box'], 500);
        }
    }

    private function generateQrCode($number_box)
    {
        $qrCode = QrCode::format('png')->size(200)
            // ->merge(public_path('img/logo-mini.png'), 0.3, true)
            ->generate($number_box);
        $fileName = 'qrcodes/assets/box/' . $number_box . '.png';
        Storage::put('public/' . $fileName, $qrCode);
        return $fileName;
    }

    public function import(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls',
            ]);

            $Excels = Excel::toArray(new BoxesImport, $request->file('file'));

            $dataBoxes = [];
            $plant = env('PLANT_CODE');
            foreach ($Excels[0] as $row) {
                $plant = !is_null($row['plant']) ? $row['plant'] : $plant;
                $statusBox = $row['status_box'] ?? 'ready';
                $colorCodeBox = !is_null($row['color_code_box']) ? $row['color_code_box'] : Box::DEFAULTCOLOR;
                $typeBox = strval($row['type_box']);
                $numberBox = $plant . "-" . $typeBox . "-" . $colorCodeBox . "-" . $row['number_box'];
                $box = new Box([
                    'number_box' => $numberBox,
                    'number_box_alias' => $row['number_box'],
                    'type_box' => $typeBox,
                    'color_code_box' => $colorCodeBox,
                    'status_box' => $statusBox,
                    'plant' => $plant,
                    'qr_code' => $this->generateQrCode($numberBox),
                    'qr_number' => $numberBox,
                ]);
                $box->save();
                $dataBoxes[] = $box;
            }

            return response()->json($dataBoxes, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'messages' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to import boxes', 'message' => $e->getMessage()], 500);
        }
    }

    public function getTimelineBox(Request $request): JsonResponse
    {
        $request->validate([
            'number_box' => 'required|string',
        ]);

        try {
            $numberBox = $request->input('number_box');
            $box = Box::where('number_box', $numberBox)->first();

            if (!$box) {
                return response()->json(['error' => 'Box not found'], 404);
            }

            $timeline = $box->getTimelineBox();

            return response()->json([
                'message' => 'Successfully retrieved box timeline',
                'data' => $timeline,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve box timeline', 'message' => $e->getMessage()], 500);
        }
    }
}
