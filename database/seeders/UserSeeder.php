<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            "name" => "superadmin",
            "username" => "superadmin",
            "password" => Hash::make("rahasia"),
            "is_active" => "Y",
            "role" => "admin",
            "phone" => "085325224829"
        ]);

        User::create([
            "name" => "Redha",
            "username" => "redha",
            "password" => Hash::make("rahasia"),
            "is_active" => "Y",
            "role" => "teknisi",
            "phone" => "08123512718"
        ]);

        User::create([
            "name" => "Kharis",
            "username" => "kharis",
            "password" => Hash::make("rahasia"),
            "is_active" => "Y",
            "role" => "member",
            "phone" => "085412671289"
        ]);
    }
}
