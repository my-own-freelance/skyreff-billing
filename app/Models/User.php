<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Table name (optional, defaultnya "users")
     */
    protected $table = 'users';

    /**
     * Kolom yang boleh diisi mass assignment
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'role',
        'phone',
        'address',
        'link_maps',
        'last_login_at',
        'is_active',
        'area_id',
    ];

    /**
     * Kolom yang harus disembunyikan saat serialisasi
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Casting atribut
     */
    protected $casts = [
        'last_login_at' => 'datetime',
        'password'      => 'hashed', // Laravel 10 fitur baru hashing otomatis
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    // Relasi ke Area
    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id');
    }

    // Relasi ke subscription
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'user_id');
    }

    // Relasi ke tiket (jika user role member bikin tiket)
    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'member_id');
    }

    // Relasi ke tiket (jika user role teknisi mengerjakan tiket)
    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'technician_id');
    }

    // Relasi ke invoice
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'user_id');
    }
}
