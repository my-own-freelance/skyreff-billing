<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';

    /**
     * Kolom yang bisa diisi mass assignment
     */
    protected $fillable = [
        'name',
        'price',
        'level',
        'image',
        'description',
        'features',
        'is_active',
    ];

    /**
     * Casting atribut
     */
    protected $casts = [
        'features'  => 'array',   // otomatis decode/encode JSON
        'is_active' => 'string',  // 'Y' atau 'N'
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Relasi ke Subscription (1 Plan bisa punya banyak Subscription)
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    // Relasi ke Invoice (1 Plan bisa muncul di banyak Invoice)
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'plan_id');
    }
}
