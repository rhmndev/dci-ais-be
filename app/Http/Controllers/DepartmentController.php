<?php

namespace App\Http\Controllers;

use App\Department;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\DepartmentImport;
use App\Exports\DepartmentExport;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Department::query();

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

            if ($request->has('show_all') && $request->show_all) {
                $departments = $query->get();
            } else {
                $perPage = $request->get('per_page', 15);
                $departments = $query->paginate($perPage);
            }

            return response()->json([
                'message' => 'success',
                'data' => $departments
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'failed',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function list()
    {
        try {
            $departments = Department::all([
                'code',
                'name',
                'alias'
            ])->sortBy('code')->values()->all();
            return response()->json([
                'message' => 'success',
                'data' => $departments
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
            $department = Department::findOrFail($id);
            return response()->json([
                'message' => 'success',
                'data' => $department
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

            $department = Department::updateOrCreate(
                ['code' => $request->code],
                [
                    'name' => $request->name,
                    'alias' => $request->alias,
                ]
            );

            $department->created_by = auth()->user()->npk;
            $department->updated_by = auth()->user()->npk;
            $department->save();

            return response()->json([
                'message' => 'success',
                'data' => $department
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
            $department = Department::findOrFail($id);
            $department->update($request->all());

            $department->updated_by = auth()->user()->npk;
            $department->save();

            return response()->json([
                'message' => 'success',
                'data' => $department
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
            $department = Department::findOrFail($id);
            $department->delete();

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
            $Excels = Excel::toArray(new DepartmentImport, $file);

            foreach ($Excels[0] as $row) {
                Department::updateOrCreate(
                    ['code' => $row['code']],
                    [
                        'name' => $row['name'],
                        'alias' => $row['alias'] ?? null,
                    ]
                );
            }

            return response()->json([
                'message' => 'Departments imported successfully'
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
        $selectedDepartments = $request->selectedDepartments;

        $departmentQuery = Department::query();

        if ($selectedDepartments && $selectedDepartments !== 0) {
            $departmentQuery->whereIn('_id', $selectedDepartments);
        }

        $departments = $departmentQuery->get();

        return Excel::download(new DepartmentExport($departments), 'department_export.xlsx');
    }
}
