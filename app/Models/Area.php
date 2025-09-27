<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    // Table name (optional, karena defaultnya "areas")
    protected $table = 'areas';

    // Mass assignment fields
    protected $fillable = [
        'name',
        'code',
        'description',
        'meta',
    ];

    // Casting field JSON ke array otomatis
    protected $casts = [
        'meta' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Relasi ke pelanggan (User role member/pelanggan)
    public function customers()
    {
        return $this->hasMany(User::class, 'area_id');
    }

    // Relasi ke pengumuman (karena pengumuman bisa target area)
    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'area_id');
    }
}
