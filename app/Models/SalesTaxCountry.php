<?php

namespace App\Models;

use App\Actions\TaxActions;
use Illuminate\Database\Eloquent\Model;

class SalesTaxCountry extends Model
{
    protected $table = 'sales_tax_countries';

    protected $fillable = [
        'country_code',
        'sales_tax_name',
        'sales_tax_rate',
        'is_active',
    ];

    protected $casts = [
        'sales_tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function states()
    {
        return $this->hasMany(SalesTaxState::class, 'country_id');
    }

    public function getActiveStates()
    {
        return $this->states()->where('is_active', true)->get();
    }

    public static function actions()
    {
        return new TaxActions;
    }
}
