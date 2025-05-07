<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;
use Carbon\Carbon;

class RoleMaterialType extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'role_material_types';

    protected $fillable = [
        'role_id',
        'material_type',
        'created_by',
        'updated_by'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Get the role that owns the material type.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the materials associated with this type.
     */
    public function materials()
    {
        return $this->hasMany(Material::class, 'type', 'material_type');
    }

    /**
     * Scope a query to only include records for a specific role.
     */
    public function scopeForRole($query, $roleId)
    {
        return $query->where('role_id', $roleId);
    }

    /**
     * Scope a query to only include records for a specific material type.
     */
    public function scopeForMaterialType($query, $materialType)
    {
        return $query->where('material_type', $materialType);
    }

    /**
     * Check if a role has access to a specific material type.
     */
    public static function hasAccess($roleId, $materialType)
    {
        return self::where('role_id', $roleId)
                  ->where('material_type', $materialType)
                  ->exists();
    }

    /**
     * Get all material types for a role.
     */
    public static function getMaterialTypesForRole($roleId)
    {
        return self::where('role_id', $roleId)
                  ->pluck('material_type')
                  ->toArray();
    }

    /**
     * Get all roles that have access to a material type.
     */
    public static function getRolesForMaterialType($materialType)
    {
        return self::where('material_type', $materialType)
                  ->pluck('role_id')
                  ->toArray();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = auth()->user()->username ?? 'system';
            $model->updated_by = auth()->user()->username ?? 'system';
        });

        static::updating(function ($model) {
            $model->updated_by = auth()->user()->username ?? 'system';
        });
    }
} 