<?php

namespace App\Models;

use App\Actions\PackageActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class Package extends Model
{
    protected $table = 'packages';

    protected $fillable = [
        'category_id',
        'connection_id',
        'slug',
        'name',
        'short_description',
        'description',
        'icon',
        'status',
        'global_quantity',
        'client_quantity',
        'data',
        'allow_notes',
        'sort_order',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function icon(): string
    {
        // if icon is null, return default icon
        if (! $this->icon or $this->icon == 'default.png') {
            return '/assets/common/img/package_placeholder.png';
        }

        // if icon is a URL, return the URL
        if (filter_var($this->icon, FILTER_VALIDATE_URL)) {
            return $this->icon;
        }

        // if the icon is a file path, return the file path
        return $this->icon;
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(PackagePrice::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(PackageFeature::class);
    }

    public function configOptions(): HasMany
    {
        return $this->hasMany(PackageConfigOption::class);
    }

    public function serverConnection(): BelongsTo
    {
        return $this->belongsTo(ServerConnection::class, 'connection_id', 'id');
    }

    public function getPackageConfig()
    {
        return $this->serverConnection->server->getPackageConfig($this);
    }

    public function getPackageConfigOptions()
    {
        return $this->serverConnection->server->getPackageConfigOptions($this);
    }

    public static function actions()
    {
        return new PackageActions;
    }

    public function data($string, $default = null)
    {
        // if the data is not set, return the default value
        if (! isset($this->data[$string])) {
            return $default;
        }

        // if the data is set, return the value
        return $this->data[$string];
    }

    public function configurableOptionCalculator(array $customOptions, int $frequencyInDays = 30)
    {
        // validate the custom options
        $this->validateConfigurableOptions($customOptions);

        $breakdown = [];
        $total = 0;
        $frequencyModified = false;

        foreach ($this->configOptions->whereIn('key', array_keys(Arr::dot($customOptions))) as $option) {
            $customValue = Arr::get($customOptions, $option->key, $option->default_value);

            // if frequency is 0, set it
            if ($frequencyInDays == 0) {
                $frequencyInDays = $option->onetime_day_equivalent ?? 365;
                $frequencyModified = true;
            }

            if (in_array($option->type, ['select', 'radio'])) {
                // check if the custom value is in the option data
                $options = $option->data['options'] ?? [];
                $item = collect($options)->firstWhere('value', $customValue);
                if ($item) {
                    $dailyPrice = max(0, (float) ($item['daily_price'] ?? 0));
                    $frequencyTotal = max(0, $dailyPrice * $frequencyInDays);
                    $breakdown[] = [
                        'label' => $option->label,
                        'key' => $option->key,
                        'value' => $customValue,
                        'total' => $frequencyTotal,
                        'frequency' => $frequencyInDays,
                        'daily_price' => $dailyPrice,
                    ];
                    $total += max(0, $frequencyTotal);
                }
            } elseif ($option->type == 'range' || $option->type == 'number') {
                $selectedValue = $customValue;

                // if the custom value is same as the free value, make the breakdown 0
                $freeValue = $option->data['free_value'] ?? 0;
                if ((int) $customValue === (int) $freeValue) {
                    $breakdown[] = [
                        'label' => $option->label,
                        'key' => $option->key,
                        'value' => $selectedValue,
                        'total' => 0,
                        'frequency' => $frequencyInDays,
                        'daily_price' => 0,
                    ];
                } else {
                    // if the custom value is more than the free value, remove the free value from the custom value
                    $billableUnits = max(0, (int) $customValue - (int) $freeValue);

                    // calculate the price based on the daily price
                    $dailyPrice = max(0, (float) ($option->data['daily_price'] ?? 0));
                    $frequencyTotal = max(0, $dailyPrice * $frequencyInDays * $billableUnits);
                    $breakdown[] = [
                        'label' => $option->label,
                        'key' => $option->key,
                        'value' => $selectedValue,
                        'total' => $frequencyTotal,
                        'frequency' => $frequencyInDays,
                        'daily_price' => $dailyPrice * $billableUnits,
                    ];
                    $total += max(0, $frequencyTotal);
                }
            } else {
                // assume option is free
                $breakdown[] = [
                    'label' => $option->label,
                    'key' => $option->key,
                    'value' => $customValue,
                    'total' => 0,
                    'frequency' => $frequencyInDays,
                    'daily_price' => 0,
                ];
            }

            // reset the frequency for the next iteration
            if ($frequencyModified) {
                // if the frequency was modified, we need to set it back to 0
                $frequencyInDays = 0;
            }
        }

        return [
            'breakdown' => $breakdown,
            'total' => $total,
        ];
    }

    public function validateConfigurableOptions(array $input): array
    {
        // check if the input is empty
        if (empty($input)) {
            return [];
        }

        // get configurable options rules as key => rules
        $configurableOptions = $this->configOptions->whereIn('key', array_keys($input));

        // put $input inside config_options array
        $input = ['config_options' => $input];

        $rules = $configurableOptions->mapWithKeys(function ($option) {
            return ['config_options.'.$option->key => $option->rules];
        })->toArray();

        // run them through validator
        $validator = Validator::make($input, $rules)->validate();

        foreach ($configurableOptions as $option) {
            // for select options, we manually check if the value exists in the options
            if (in_array($option->type, ['select', 'radio']) && isset($validator['config_options'][$option->key])) {
                $value = $validator['config_options'][$option->key];
                $options = collect($option->data['options'] ?? []);

                // check if the value exists in the options
                if (! $options->contains('value', $value)) {
                    throw ValidationException::withMessages([
                        'config_options.'.$option->key => "The selected value for {$option->label} is invalid, possible values are: ".$options->pluck('value')->implode(', '),
                    ]);
                }
            }

            // for number and range options, we check if the value is numeric and falls within the specified range
            if (($option->type === 'number' || $option->type === 'range') && isset($validator['config_options'][$option->key])) {
                $value = $validator['config_options'][$option->key];
                $min = $option->data['min'] ?? $option->data['min_value'] ?? null;
                $max = $option->data['max'] ?? $option->data['max_value'] ?? null;
                $freeValue = $option->data['free_value'] ?? 0;
                $isFree = $value == $freeValue;

                // check if the value is numeric
                if (! is_numeric($value)) {
                    throw ValidationException::withMessages([
                        'config_options.'.$option->key => "The value for {$option->label} must be a number.",
                    ]);
                }

                // check if the value is within the range
                if (($min !== null && $value < $min) || ($max !== null && $value > $max)) {
                    throw ValidationException::withMessages([
                        'config_options.'.$option->key => "The value for {$option->label} must be between {$min} and {$max}.",
                    ]);
                }
            }
        }

        // return the validated input
        return $validator;
    }

    public function scopeSearch($query, string $search)
    {
        if ($search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%');
            });
        }

        return $query;
    }

    public function isVisibleToUser($user = null): bool
    {
        $isAdmin = $user && $user->isAdmin();

        return match ($this->status) {
            'active', 'unlisted' => true,
            'restricted' => $isAdmin,
            default => false,
        };
    }

    public function isListedForUser($user = null): bool
    {
        $isAdmin = $user && $user->isAdmin();

        return match ($this->status) {
            'active' => true,
            'restricted' => $isAdmin,
            default => false,
        };
    }

    public function scopeVisibleToUser(Builder $query, $user = null, bool $includeUnlisted = true): Builder
    {
        $isAdmin = $user && $user->isAdmin();

        return $query->where(function (Builder $query) use ($isAdmin, $includeUnlisted) {
            $query->where('status', 'active');

            if ($includeUnlisted) {
                $query->orWhere('status', 'unlisted');
            }

            if ($isAdmin) {
                $query->orWhere('status', 'restricted');
            }
        });
    }
}
