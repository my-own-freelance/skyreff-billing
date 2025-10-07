<?php

namespace Database\Seeders;

use App\Models\BroadcastTemplate;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BroadcastTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $masterBroadcastTemplate = [
            [
                "name" => "Pra Register",
                "code" => "pra-register",
                "content" => "Pra Register",
                "is_active" => "Y"
            ],
            [
                "name" => "Aktivasi Paket",
                "code" => "aktivasi-paket",
                "content" => "Aktivasi Paket",
                "is_active" => "Y"
            ],
            [
                "name" => "Invoice Sukses Bayar",
                "code" => "invoice-sukses-bayar",
                "content" => "Invoice Sukses Bayar",
                "is_active" => "Y"
            ],
            [
                "name" => "Invoice Baru Keluar",
                "code" => "invoice-baru",
                "content" => "Invoice Baru Keluar",
                "is_active" => "Y"
            ],
            [
                "name" => "Invoice Expired",
                "code" => "invoice-expired",
                "content" => "Invoice Expired
                ",
                "is_active" => "Y"
            ],
            [
                "name" => "Tiket Teknisi Baru",
                "code" => "tiket-teknisi-baru",
                "content" => "Tiket Teknisi Baru",
                "is_active" => "Y"
            ],


        ];
        foreach ($masterBroadcastTemplate as $template) {
            BroadcastTemplate::create($template);
        }
    }
}
