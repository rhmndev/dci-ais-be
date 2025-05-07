<?php

namespace App\Http\Controllers;

use App\RoleMaterialType;
use App\Role;
use App\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleMaterialTypeController extends Controller
{
    /**
     * Display a listing of role material types.
     */
    public function index(Request $request)
    {
        try {
            $query = RoleMaterialType::query();

            // Filter by role_id if provided
            if ($request->has('role_id')) {
                $query->where('role_id', $request->role_id);
            }

            // Filter by material_type if provided
            if ($request->has('material_type')) {
                $query->where('material_type', $request->material_type);
            }

            // Include role and materials relationships
            $query->with(['role', 'materials']);

            // Paginate results
            $perPage = $request->get('per_page', 15);
            $roleMaterialTypes = $query->paginate($perPage);

            return response()->json([
                'type' => 'success',
                'data' => $roleMaterialTypes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error fetching role material types: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created role material type.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,_id',
            'material_type' => 'required|string|exists:materials,type'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'type' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Check if relationship already exists
            $exists = RoleMaterialType::where('role_id', $request->role_id)
                                    ->where('material_type', $request->material_type)
                                    ->exists();

            if ($exists) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'This role-material type relationship already exists'
                ], 409);
            }

            $roleMaterialType = new RoleMaterialType();
            $roleMaterialType->role_id = $request->role_id;
            $roleMaterialType->material_type = $request->material_type;
            $roleMaterialType->created_by = auth()->user()->npk;
            $roleMaterialType->updated_by = auth()->user()->npk;
            $roleMaterialType->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Role-Material Type relationship created successfully',
                'data' => $roleMaterialType
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error creating role material type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified role material type.
     */
    public function show($id)
    {
        try {
            $roleMaterialType = RoleMaterialType::with(['role', 'materials'])->findOrFail($id);
            
            return response()->json([
                'type' => 'success',
                'data' => $roleMaterialType
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Role material type not found'
            ], 404);
        }
    }

    /**
     * Update the specified role material type.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'sometimes|required|exists:roles,_id',
            'material_type' => 'sometimes|required|string|exists:materials,type'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'type' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $roleMaterialType = RoleMaterialType::findOrFail($id);

            if ($request->has('role_id')) {
                $roleMaterialType->role_id = $request->role_id;
            }

            if ($request->has('material_type')) {
                $roleMaterialType->material_type = $request->material_type;
            }

            $roleMaterialType->updated_by = auth()->user()->username;
            $roleMaterialType->save();

            return response()->json([
                'type' => 'success',
                'message' => 'Role-Material Type relationship updated successfully',
                'data' => $roleMaterialType
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error updating role material type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified role material type.
     */
    public function destroy($id)
    {
        try {
            $roleMaterialType = RoleMaterialType::findOrFail($id);
            $roleMaterialType->delete();

            return response()->json([
                'type' => 'success',
                'message' => 'Role-Material Type relationship deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error deleting role material type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all material types for a specific role.
     */
    public function getByRole($roleId)
    {
        try {
            $materialTypes = RoleMaterialType::where('role_id', $roleId)
                                           ->with('materials')
                                           ->get();
            
            return response()->json([
                'type' => 'success',
                'data' => $materialTypes
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error fetching material types for role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all roles that have access to a specific material type.
     */
    public function getByMaterialType($materialType)
    {
        try {
            $roles = RoleMaterialType::where('material_type', $materialType)
                                   ->with('role')
                                   ->get();
            
            return response()->json([
                'type' => 'success',
                'data' => $roles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error fetching roles for material type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk assign material types to a role.
     */
    public function bulkAssign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id' => 'required|exists:roles,_id',
            'material_types' => 'required|array',
            'material_types.*' => 'required|string|exists:materials,type'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'type' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $created = [];
            foreach ($request->material_types as $materialType) {
                $roleMaterialType = RoleMaterialType::firstOrCreate(
                    [
                        'role_id' => $request->role_id,
                        'material_type' => $materialType
                    ],
                    [
                        'created_by' => auth()->user()->username,
                        'updated_by' => auth()->user()->username
                    ]
                );
                $created[] = $roleMaterialType;
            }

            return response()->json([
                'type' => 'success',
                'message' => 'Material types assigned to role successfully',
                'data' => $created
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'Error bulk assigning material types: ' . $e->getMessage()
            ], 500);
        }
    }
} 