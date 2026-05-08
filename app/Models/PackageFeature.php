<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageFeature extends Model
{
    protected $table = 'package_features';

    protected $fillable = [
        'package_id',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'package_id' => 'integer',
            'description' => 'string',
            'sort_order' => 'integer',
        ];
    }

    public function package(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}
