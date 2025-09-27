<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceFaq extends Model
{
    use HasFactory;
    protected $table = 'device_faqs';

    /**
     * Kolom yang bisa diisi mass assignment
     */
    protected $fillable = [
        'device_id',
        'question',
        'answer',
        'order',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Relasi ke Device (FAQ belongs to Device)
    public function device()
    {
        return $this->belongsTo(Device::class, 'device_id');
    }
}
