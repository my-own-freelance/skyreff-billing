<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mutation extends Model
{
    use HasFactory;

    protected $table = 'mutations';

    protected $fillable = [
        'code',
        'amount',
        'type',
        'first_commission',
        'last_commission',
        'bank_name',
        'bank_account',
        'status',
        'proof_of_payment',
        'user_id',
    ];

    /**
     * Relasi ke user (teknisi).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
