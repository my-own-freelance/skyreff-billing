<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'subscription_number',
        'type',
        'username',
        'password',
        'queue',
        'current_period_start',
        'current_period_end',
        'next_invoice_at',
        'plan_id',
        'user_id',
        'meta',
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end'   => 'datetime',
        'next_invoice_at'      => 'datetime',
        'meta'                 => 'array',
    ];

    /**
     * Relasi ke Plan
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function devices()
    {
        return $this->belongsToMany(Device::class, 'device_subscriptions');
    }
}
