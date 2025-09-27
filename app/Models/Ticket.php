<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'status',
        'cases',
        'solution',
        'member_id',
        'technician_id',
        'created_by',
        'subscription_id',
        'complaint_image',
        'completion_image',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Relasi ke Member (User)
     */
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Relasi ke Teknisi (User)
     */
    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    /**
     * Relasi ke User yang membuat tiket
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke Subscription
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Scope filter status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope filter type
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Apakah tiket sudah selesai
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['success', 'failed', 'reject']);
    }
}
