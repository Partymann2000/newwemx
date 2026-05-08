<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'name',
        'description',
        'super_admin',
        'parent_id',
    ];

    public function permissions()
    {
        return $this->hasMany(RolePermission::class, 'role_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_user', 'role_id', 'user_id');
    }

    // should return all permissions of this role including inherited from parent roles
    public function getAllPermissions()
    {
        $permissions = $this->permissions()->pluck('permission')->toArray();

        if ($this->parent) {
            $parent = $this->parent;

            while ($parent) {
                $parentPermissions = $parent->permissions()->pluck('permission')->toArray();
                $permissions = array_merge($permissions, $parentPermissions);
                $parent = $parent->parent;
            }
        }

        return array_unique($permissions);
    }

    public function parent()
    {
        return $this->belongsTo(Role::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Role::class, 'parent_id');
    }
}
