<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackagePrice extends Model
{
    protected $table = 'package_prices';

    protected $fillable = [
        'package_id',
        'short_description',
        'period_in_days',
        'price',
        'setup_fee',
        'upgrade_fee',
        'data',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:8',
            'setup_fee' => 'decimal:8',
            'upgrade_fee' => 'decimal:8',
            'data' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function initialPrice(): string
    {
        return $this->price + $this->setup_fee;
    }

    public function cycle(): string
    {
        return daysToPeriod($this->period_in_days);
    }

    public function isRecurring(): bool
    {
        return $this->period_in_days > 0;
    }

    public function isOneTime(): bool
    {
        return $this->period_in_days === 0;
    }

    public function getDailyPrice()
    {
        if (! $this->isRecurring()) {
            return $this->price;
        }

        return $this->price / $this->period_in_days;
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function scopeSearch($query, string $search): void
    {
        if ($search) {
            $query->where('period_in_days', 'like', '%'.$this->search.'%')
                ->orWhere('setup_fee', 'like', '%'.$this->search.'%')
                ->orWhere('upgrade_fee', 'like', '%'.$this->search.'%')
                ->orWhere('is_active', 'like', '%'.$this->search.'%')
                ->orWhere('sort_order', 'like', '%'.$this->search.'%');
        }
    }
}
