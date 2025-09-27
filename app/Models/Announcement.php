<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'message',
        'type',
        'is_active',
        'user_id',
        'area_id',
    ];

    /**
     * Relasi ke User (penerima khusus / dibuat oleh user tertentu)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Area (jika announcement ditujukan ke area tertentu)
     */
    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * Scope hanya yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', 'Y');
    }

    /**
     * Scope filter berdasarkan tipe
     */
    public function scopeType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Apakah pengumuman aktif
     */
    public function isActive(): bool
    {
        return $this->is_active === 'Y';
    }
}
