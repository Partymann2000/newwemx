<?php

namespace App\Models;

use App\Facades\World;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'company_name',
        'tax_id',
        'address',
        'address2',
        'country',
        'region',
        'city',
        'zip_code',
    ];

    protected static function booted()
    {
        static::updating(function ($address) {
            // Log the changes made to the address model
            self::logAddressUpdates($address);
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCountryNameAttribute()
    {
        return World::getCountry($this->country);
    }

    /**
     * This method logs an activity for the user.
     *
     * @param array $data
     *
     * @return ActivityLog
     */
    public function logActivity(array $data)
    {
        return ActivityLog::create($data);
    }

    /**
     * This method retrieves the activity logs for the address.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function activityLogs()
    {
        // return from morps to many relationship
        return $this->morphMany(ActivityLog::class, 'model')
            ->orderBy('created_at', 'desc');
    }

    /**
     * This method retrieves the count of activity logs for a specific field.
     *
     * @param string $field
     *
     * @return int
     */
    public function getActivityLogCountForField(string $field)
    {
        return $this->activityLogs()
            ->where('field', $field)
            ->count();
    }

    /**
     * This method logs the changes made to the order model.
     * It checks if the user is dirty (i.e., has unsaved changes)
     * and if so, it logs the changes made to the specified fields.
     *
     * @param \App\Models\Address $address
     *
     * @return void
     */
    public static function logAddressUpdates($address): void
    {
        $fieldsToLog = [
            'company_name',
            'tax_id',
            'address',
            'address2',
            'country',
            'region',
            'city',
            'zip_code',
        ];

        foreach ($fieldsToLog as $field) {
            if ($address->isDirty($field)) {
                $causer = auth()->user() ?? $address->user;
                $oldValue = $address->getOriginal($field);
                $newValue = $address->$field;

                // Log the change
                $address->logActivity([
                    'user_id' => $causer->id,
                    'event' => "address.updated.{$field}",
                    'description' => "Address {$field} updated by {$causer->username}",
                    'field' => $field,
                    'model_type' => Address::class,
                    'model_id' => $address->id,
                    'old_value' => $oldValue,
                    'new_value' => $newValue,
                ]);
            }
        }
    }
}
