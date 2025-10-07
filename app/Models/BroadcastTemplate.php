<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BroadcastTemplate extends Model
{
    use HasFactory;

    // Mass assignment fields
    protected $fillable = [
        'name',
        'code',
        'content',
        'is_active',
    ];
}
