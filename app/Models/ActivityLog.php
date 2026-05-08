<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'event',
        'description',
        'field',
        'tag',
        'model_type',
        'model_id',
        'old_value',
        'new_value',
        'ip_address',
        'properties',
        'request',
    ];

    protected $casts = [
        'properties' => 'array',
        'request' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($log) {
            if($request = request()) {
                // automatically define user id
                if (empty($log->ip_address)) {
                    $log->ip_address = $request->ip();
                }

                // automatically define request data
                if (empty($log->request)) {
                    $requestData = [
                        'user_agent' => $request->userAgent(),
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'headers' => $request->headers->all(),
                    ];

                    $log->request = $requestData;
                }
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function model()
    {
        return $this->morphTo();
    }
}
