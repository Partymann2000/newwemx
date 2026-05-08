<?php

namespace App\Models;

use App\Actions\SettingsActions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * App\Models\Setting
 *
 * @property int $id
 * @property string $name
 * @property string|null $type
 * @property string|null $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Setting newModelQuery()
 * @method static Builder|Setting newQuery()
 * @method static Builder|Setting query()
 * @method static Builder|Setting whereCreatedAt($value)
 * @method static Builder|Setting whereData($value)
 * @method static Builder|Setting whereId($value)
 * @method static Builder|Setting whereName($value)
 * @method static Builder|Setting whereType($value)
 * @method static Builder|Setting whereUpdatedAt($value)
 *
 * @property string $key
 * @property string|null $value
 *
 * @method static Builder|Setting whereKey($value)
 * @method static Builder|Setting whereValue($value)
 *
 * @mixin \Eloquent
 */
class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = ['key', 'value'];

    protected static $settings = null;

    public static function getAllSettings(): ?array
    {
        if (self::$settings === null) {
            try {
                self::$settings = Setting::query()->pluck('value', 'key')->all();
            } catch (\Exception $e) {
                self::$settings = [];
            }
        }

        return self::$settings;
    }

    /**
     * @return mixed|null
     */
    public static function get(mixed $key, mixed $default = null): mixed
    {
        $settings = self::getAllSettings();

        // ensure that encrypted keys are decrypted
        // when they are returned
        if (Str::contains($key, 'encrypted')) {
            if (self::has($key)) {
                return decrypt($settings[$key]);
            }

            return $default;
        }

        return $settings[$key] ?? $default;
    }

    public static function store(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::put($key, $value);
        }
    }

    public static function has($key): bool
    {
        return array_key_exists($key, self::getAllSettings());
    }

    public static function put(string $key, $value): void
    {
        // If the value is an array, convert it to JSON
        if (is_array($value)) {
            $value = json_encode($value);
        }

        // ensure that encrypted keys are
        // encrypted before being stored
        if (Str::contains($key, 'encrypted')) {
            $value = encrypt($value);
        }

        try {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
            self::$settings = null;
        } catch (\Exception $e) {

        }
    }

    public static function forget(string $key): void
    {
        Setting::query()->where('key', $key)->delete();
        self::$settings = null;
    }

    public static function actions(): SettingsActions
    {
        return new SettingsActions;
    }
}
