<?php

namespace App\Http\Controllers;

use App\Exports\SubcontExport;
use App\Imports\SubcontImport;
use App\SubCont;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SubContController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15); // Default to 15 items per page if not specified
            $query = SubCont::query();

            if ($request->has('code')) {
                $query->where('code', 'like', '%' . $request->code . '%');
            }

            if ($request->has('name')) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->has('keyword') && $request->search != '') {
                $searchTerm = $request->search;
                $query->where(function ($query) use ($searchTerm) {
                    $query->where('code', 'like', '%' . $searchTerm . '%')
                        ->orWhere('name', 'like', '%' . $searchTerm . '%');
                });
            }

            if ($request->has('show_all') && $request->show_all) {
                $subconts = $query->get(); // Return all records without pagination
            } else {
                $perPage = $request->get('per_page', 15); // Default to 15 items per page
                $subconts = $query->paginate($perPage);
            }

            return response()->json([
                'message' => 'success',
                'data' => $subconts
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
            $subcont = SubCont::findOrFail($id);
            return response()->json([
                'message' => 'success',
                'data' => $subcont
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

            $subcont = SubCont::updateOrCreate(
                ['code' => $request->code],
                [
                    'name' => $request->name,
                    'description' => $request->description,
                ]
            );

            $subcont->created_by = auth()->user()->npk;
            $subcont->updated_by = auth()->user()->npk;
            $subcont->save();
            return response()->json([
                'message' => 'success',
                'data' => $subcont
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
            $subcont = SubCont::findOrFail($id);
            $subcont->update($request->all());

            // updated_by
            $subcont->updated_by = auth()->user()->npk;
            $subcont->save();
            return response()->json([
                'message' => 'success',
                'data' => $subcont
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
            $subcont = SubCont::findOrFail($id);
            $subcont->delete();
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
            $Excels = Excel::toArray(new SubcontImport, $file);

            foreach ($Excels[0] as $row) {
                $Subcont = SubCont::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'name' => $row['name'],
                        'description' => $row['description'],
                    ]
                );
            }

            return response()->json([
                'message' => 'Subconts imported successfully'
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
        $selectedSubConts = $request->selectedSubConts;

        $subcontQuery = SubCont::query();

        if ($selectedSubConts && $selectedSubConts !== 0) {
            $subcontQuery->whereIn('_id', $selectedSubConts);
        }

        // Get the selected or all subcont
        $subconts = $subcontQuery->get();

        // Generate the Excel file and return it as a download directly
        return Excel::download(new SubcontExport($subconts), 'subcont_export.xlsx');
    }
}
