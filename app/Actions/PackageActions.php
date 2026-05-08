<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Package;
use App\Models\PackageConfigOption;
use App\Models\PackageFeature;
use App\Models\PackagePrice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PackageActions extends Action
{
    /**
     * Create a new package
     *
     * @throws ValidationException
     */
    public static function createPackageAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'category_id' => ['required', 'exists:categories,id'],
            'connection_id' => ['required', 'exists:server_connections,id'],
            'name' => ['required', 'string', 'max:255', 'unique:packages,name'],
            'slug' => ['sometimes', 'max:255', 'unique:packages,slug'],
            'status' => ['required', 'string', 'max:255', 'in:restricted,unlisted,active,inactive'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ])->validate();

        if (! isset($validatedData['slug'])) {
            $validatedData['slug'] = rand(1000000, 9999999);
        }

        // if icon is not set, set to default
        if (! isset($validatedData['icon'])) {
            $validatedData['icon'] = '/assets/common/img/package_placeholder.png';
        }

        return DB::transaction(function () use ($validatedData) {
            $package = Package::create(self::omitNullValues($validatedData));

            $package->prices()->create([
                'short_description' => 'Monthly',
                'period_in_days' => 30,
                'price' => 0,
            ]);

            return $package;
        });
    }

    /**
     * Update a package
     *
     * @throws ValidationException
     */
    public static function updatePackageAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'package_id' => ['required', 'exists:packages,id'],
            'category_id' => ['sometimes', 'required', 'exists:categories,id'],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'unique:packages,slug,'.$input['package_id']],
            'status' => ['sometimes', 'required', 'string', 'max:255', 'in:restricted,unlisted,active,inactive'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'global_quantity' => ['sometimes', 'required', 'integer'],
            'client_quantity' => ['sometimes', 'required', 'integer'],
        ])->validate();

        $package = Package::find($input['package_id']);

        if (! $package) {
            throw ValidationException::withMessages([
                'package_id' => 'Package not found',
            ]);
        }

        unset($validatedData['package_id']);

        return $package->update(self::omitNullValues($validatedData));
    }

    /**
     * Store Server package data
     *
     * @throws ValidationException
     */
    public static function storePackageDataPackageAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'package_id' => ['required', 'exists:packages,id'],
            'data' => ['nullable', 'array'],
        ])->validate();

        $package = Package::find($input['package_id']);

        if (! $package) {
            throw ValidationException::withMessages([
                'package_id' => 'Package not found',
            ]);
        }

        $configRules = $package->serverConnection->server->getPackageConfigRules($package);

        $validatedPackageData = Validator::make($input['data'], $configRules)->validate();

        $package->update([
            'data' => $validatedPackageData,
        ]);
    }

    public static function createFeatureAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'package_id' => ['required', 'exists:packages,id'],
            'description' => ['required', 'string', 'max:255'],
        ])->validate();

        $package = Package::find($input['package_id']);

        if (! $package) {
            throw ValidationException::withMessages([
                'package_id' => 'Package not found',
            ]);
        }

        // unset package_id from validated data to avoid mass assignment issue
        unset($validatedData['package_id']);

        return $package->features()->create(self::omitNullValues($validatedData));
    }

    public static function updateFeatureAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'feature_id' => ['required', 'exists:package_features,id'],
            'description' => ['sometimes', 'required', 'string', 'max:255'],
        ])->validate();

        $feature = PackageFeature::find($input['feature_id']);

        if (! $feature) {
            throw ValidationException::withMessages([
                'feature_id' => 'Feature not found',
            ]);
        }

        unset($validatedData['feature_id']);

        return $feature->update(self::omitNullValues($validatedData));
    }

    public static function deleteFeatureAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'feature_id' => ['required', 'exists:package_features,id'],
        ])->validate();

        $feature = PackageFeature::find($input['feature_id']);

        if (! $feature) {
            throw ValidationException::withMessages([
                'feature_id' => 'Feature not found',
            ]);
        }

        return $feature->delete();
    }

    /**
     * Create package price
     *
     * @throws ValidationException
     */
    public static function createPackagePriceAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'package_id' => ['required', 'exists:packages,id'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'period_in_days' => ['required', 'integer'],
            'price' => ['required', 'numeric'],
            'setup_fee' => ['sometimes', 'numeric'],
            'upgrade_fee' => ['sometimes', 'numeric'],
            'data' => ['nullable', 'array'],
        ])->validate();

        return PackagePrice::create(self::omitNullValues($validatedData));
    }

    public static function updatePackagePriceAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'price_id' => ['required', 'exists:package_prices,id'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'period_in_days' => ['sometimes', 'required', 'integer'],
            'price' => ['sometimes', 'required', 'numeric'],
            'setup_fee' => ['sometimes', 'numeric'],
            'upgrade_fee' => ['sometimes', 'numeric'],
            'data' => ['nullable', 'array'],
        ])->validate();

        $price = PackagePrice::find($input['price_id']);

        if (! $price) {
            throw ValidationException::withMessages([
                'price_id' => 'Price not found',
            ]);
        }

        unset($validatedData['price_id']);

        return $price->update(self::omitNullValues($validatedData));
    }

    /**
     * Create Config Options for a Package as an Admin.
     *
     * @throws ValidationException
     */
    public static function createConfigOptionAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'package_id' => 'required|exists:packages,id',
            'key' => 'required|string|max:255',
            'label' => 'required|string',
            'description' => 'nullable|string',
            'rules' => 'nullable|string',
            'default_value' => 'nullable',
            'onetime_day_equivalent' => ['nullable', 'integer', 'min:0'],
            'type' => ['nullable', 'string', 'in:text,textarea,select,radio,range,number,email,password'],
            'data' => ['nullable', 'array'],
        ])->validate();

        $package = Package::findOrFail($validatedData['package_id']);

        if (! $package) {
            throw ValidationException::withMessages(['package_id' => 'Package not found.']);
        }

        // Check if the key already exists in the package's config options
        if ($package->configOptions()->where('key', $validatedData['key'])->exists()) {
            throw ValidationException::withMessages(['key' => 'Config option for this key already exists.']);
        }

        $type = $validatedData['type'] ?? $input['type'] ?? 'text';
        $rules = $validatedData['rules'] ?? $input['rules'] ?? 'required';
        $defaultValue = $validatedData['default_value'] ?? $input['default_value'] ?? '';
        $onetimeDays = $validatedData['onetime_day_equivalent'] ?? $input['onetime_day_equivalent'] ?? 365;
        $data = $validatedData['data'] ?? $input['data'] ?? [];

        return $package->configOptions()->create([
            'label' => $validatedData['label'],
            'description' => $validatedData['description'] ?? '',
            'key' => $validatedData['key'],
            'type' => $type,
            'rules' => $rules,
            'default_value' => $defaultValue === null ? '' : (string) $defaultValue,
            'onetime_day_equivalent' => $onetimeDays,
            'data' => $data,
        ]);
    }

    /**
     * Update Config Options for a Package as an Admin.
     *
     * @throws ValidationException
     */
    public static function updateConfigOptionAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'config_option_id' => ['required', 'exists:package_config_options,id'],
            'label' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'rules' => ['nullable', 'string', 'max:255'],
            'default_value' => ['sometimes', 'required', 'string', 'max:255'],
            'onetime_day_equivalent' => ['nullable', 'integer', 'min:0'],
            'type' => ['nullable', 'string', 'in:text,textarea,select,radio,range,number,email,password'],
            'data' => ['nullable', 'array'],
            'daily_price' => ['nullable', 'numeric', 'min:0'],
            'free_value' => ['nullable', 'max:255'],
        ])->validate();

        $option = PackageConfigOption::findOrFail($validatedData['config_option_id']);

        if (! $option) {
            throw ValidationException::withMessages(['config_option_id' => 'Config option not found.']);
        }

        unset($validatedData['config_option_id']);

        return $option->update(self::omitNullValues($validatedData));
    }

    /**
     * Delete Config Options for a Package as an Admin.
     *
     * @throws ValidationException
     */
    public static function deleteConfigOptionAsAdmin(array $input)
    {
        $validatedData = Validator::make($input, [
            'config_option_id' => ['required', 'exists:package_config_options,id'],
        ])->validate();

        $option = PackageConfigOption::findOrFail($validatedData['config_option_id']);

        if (! $option) {
            throw ValidationException::withMessages(['config_option_id' => 'Config option not found.']);
        }

        return $option->delete();
    }

    /**
     * Delete a package as an admin.
     *
     * A package can only be deleted once every related order
     * has been terminated by an admin.
     *
     * @throws ValidationException
     */
    public static function deletePackageAsAdmin(array $input): bool
    {
        $validatedData = Validator::make($input, [
            'package_id' => ['required', 'exists:packages,id'],
        ])->validate();

        $package = Package::find($validatedData['package_id']);

        if (! $package) {
            throw ValidationException::withMessages([
                'package_id' => 'Package not found',
            ]);
        }

        return DB::transaction(function () use ($package): bool {
            $activeOrderIds = Order::query()
                ->where('package_id', $package->id)
                ->where('status', '!=', 'terminated')
                ->pluck('id');

            if ($activeOrderIds->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'package_id' => 'This package cannot be deleted until all related orders are terminated. Open orders: #'.$activeOrderIds->implode(', #'),
                ]);
            }

            Order::query()
                ->where('package_id', $package->id)
                ->delete();

            return $package->delete();
        });
    }
}
