<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;
    protected $table = 'devices';

    /**
     * Kolom yang bisa mass assignment
     */
    protected $fillable = [
        'name',
        'excerpt',
        'is_active',
        'image',
        'description',
    ];

    /**
     * Casting atribut
     */
    protected $casts = [
        'is_active' => 'string', // 'Y' atau 'N'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Relasi ke FAQ Device (One to Many)
    public function faqs()
    {
        return $this->hasMany(DeviceFaq::class, 'device_id');
    }

    // Relasi ke Subscription melalui pivot (Many to Many)
    public function subscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'device_subscription')
            ->withPivot(['serial_number', 'meta'])
            ->withTimestamps();
    }
}
