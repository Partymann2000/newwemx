<?php

namespace App\Models;

use App\Events\ServerAccounts\AccountCreated;
use App\Events\ServerAccounts\AccountDeleted;
use App\Events\ServerAccounts\AccountUpdated;
use Illuminate\Database\Eloquent\Model;

class ServerAccount extends Model
{
    protected $table = 'server_accounts';

    protected $fillable = [
        'user_id',
        'order_id',
        'server',
        'external_id',
        'username',
        'password',
        'data',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'data' => 'array',
    ];

    protected $hidden = [
        'password',
    ];

    protected $dispatchesEvents = [
        'created' => AccountCreated::class,
        'deleted' => AccountDeleted::class,
        'updated' => AccountUpdated::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
