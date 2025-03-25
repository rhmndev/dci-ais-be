<?php

namespace App\Http\Controllers;

use App\Imports\MachineImport;
use App\Machine;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MachineController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15); // Default to 15 items per page if not specified
            $query = Machine::query();

            if ($request->has('code')) {
                $query->where('code', 'like', '%' . $request->code . '%');
            }

            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->has('search') && $request->search != '') {
                $searchTerm = $request->search;
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('code', 'like', '%' . $searchTerm . '%')
                        ->orWhere('name', 'like', '%' . $searchTerm . '%');
                });
            }

            $machines = $query->paginate($perPage);

            return response()->json([
                'message' => 'success',
                'data' => $machines
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $machine = Machine::findOrFail($id);
            return response()->json([
                'message' => 'success',
                'data' => $machine
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
        try {
            $request->validate([
                'code' => 'required',
                'name' => 'required',
            ]);

            $machine = Machine::updateOrCreate(
                ['code' => $request->code],
                [
                    'name' => $request->name,
                    'description' => $request->description,
                ]
            );

            $machine->created_by = auth()->user()->npk;
            $machine->updated_by = auth()->user()->npk;
            $machine->save();
            return response()->json([
                'message' => 'success',
                'data' => $machine
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $machine = Machine::findOrFail($id);
            $machine->update($request->all());

            // updated_by
            $machine->updated_by = auth()->user()->npk;
            $machine->save();
            return response()->json([
                'message' => 'success',
                'data' => $machine
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
            $machine = Machine::findOrFail($id);
            $machine->delete();
            return response()->json([
                'message' => 'success',
                'data' => null
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
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            $file = $request->file('file');
            $Excels = Excel::toArray(new MachineImport, $file);

            foreach ($Excels[0] as $row) {
                $Machine = Machine::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'name' => $row['name'],
                        'description' => $row['description'],
                    ]
                );
            }

            return response()->json([
                'message' => 'Machines imported successfully'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
