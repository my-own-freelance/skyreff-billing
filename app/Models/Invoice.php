<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'invoice_number',
        'amount',
        'invoice_period_start',
        'invoice_period_end',
        'due_date',
        'subscription_id',
        'plan_id',
        'user_id',
        'metadata',
        'paid_at',
        'payment_data',
    ];

    protected $casts = [
        'invoice_period_start' => 'datetime',
        'invoice_period_end'   => 'datetime',
        'due_date'             => 'datetime',
        'paid_at'              => 'datetime',
        'metadata'             => 'array',
        'payment_data'         => 'array',
    ];

    /**
     * Relasi ke Subscription
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

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

    /**
     * Scope untuk filter status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Cek apakah invoice sudah expired
     */
    public function isExpired(): bool
    {
        return $this->status === 'expired' ||
            ($this->due_date && $this->due_date->isPast() && $this->status === 'unpaid');
    }

    /**
     * Tandai invoice sebagai paid
     */
    public function markAsPaid(array $paymentData = []): void
    {
        $this->update([
            'status'       => 'paid',
            'paid_at'      => now(),
            'payment_data' => $paymentData,
        ]);
    }
}
