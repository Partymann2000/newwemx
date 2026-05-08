<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageConfigOption extends Model
{
    protected $table = 'package_config_options';

    protected $fillable = [
        'package_id',
        'label',
        'description',
        'key',
        'type',
        'default_value',
        'rules',
        'onetime_day_equivalent',
        'data',
        'order_order',
    ];

    protected $casts = [
        'data' => 'array',
        'is_onetime' => 'boolean',
        'onetime_day_equivalent' => 'integer',
        'order_order' => 'integer',
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
