<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTaxDetail extends Model
{
    protected $table = 'payment_tax_details';

    protected $fillable = [
        'payment_id',
        'company_name',
        'tax_id',
        'address',
        'address2',
        'city',
        'region',
        'zip_code',
        'country',
        'tax_name',
        'tax_rate',
        'tax_exempt',
        'tax_exempt_reason',
    ];

    protected $casts = [
        'tax_rate' => 'decimal:2',
        'tax_exempt' => 'boolean',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }
}
