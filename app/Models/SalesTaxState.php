<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTaxState extends Model
{
    protected $table = 'sales_tax_states';

    protected $fillable = [
        'country_id',
        'state_code',
        'sales_tax_name',
        'sales_tax_rate',
        'is_active',
    ];

    protected $casts = [
        'sales_tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function country()
    {
        return $this->belongsTo(SalesTaxCountry::class, 'country_id');
    }
}
