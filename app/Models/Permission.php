<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'permission',
        'description',
    ];

    public $timestamps = false;

    public $incrementing = false;

    public static function getAllPermissionsByGroup(): array
    {
        $permissions = Permission::pluck('description', 'permission')->toArray();
        $grouped = [];

        foreach ($permissions as $key => $value) {
            // Split the permission key by dot
            $parts = explode('.', $key);

            // Take the second part as the group (e.g., "users", "orders", "payments")
            $group = $parts[1] ?? 'misc';

            // Push into grouped array
            $grouped[$group][$key] = $value;
        }

        return $grouped;
    }
}
