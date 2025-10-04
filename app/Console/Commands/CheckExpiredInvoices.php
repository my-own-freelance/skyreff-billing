<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CheckExpiredInvoices extends Command
{
    /**
     * Nama dan signature command.
     */
    protected $signature = 'invoice:check-expired';

    /**
     * Deskripsi command.
     */
    protected $description = 'Cek invoice unpaid yang sudah lewat due_date, ubah jadi expired & kirim notifikasi';

    /**
     * Eksekusi command.
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $now = Carbon::now();

            // Cari invoice unpaid yang due_date sudah lewat
            $invoices = Invoice::with(['user', 'plan'])
                ->where('status', 'unpaid')
                ->whereNotNull('due_date')
                ->where('due_date', '<', $now)
                ->get();

            if ($invoices->isEmpty()) {
                $this->info('Tidak ada invoice yang expired.');
                DB::commit();
                return;
            }

            foreach ($invoices as $invoice) {
                $invoice->status = 'expired';
                $invoice->save();

                $member = $invoice->user;

                if ($member) {
                    $message = "Halo {$member->name},\n\n";
                    $message .= "Invoice Anda *{$invoice->invoice_number}* telah *expired* karena belum dibayar.\n\n";
                    $message .= "*Jumlah:* Rp " . number_format($invoice->amount, 0, ',', '.') . "\n";
                    $message .= "*Paket:* {$invoice->plan->name}\n";
                    $message .= "*Jatuh Tempo:* " . Carbon::parse($invoice->due_date)
                        ->timezone('Asia/Jakarta')
                        ->locale('id')
                        ->translatedFormat('d M Y H:i') . "\n\n";
                    $message .= "Segera lakukan pembayaran agar layanan tetap aktif. 🙏";

                    $payload = [
                        "appkey"   => "6879d35c-268e-4e2a-ae43-15528fc86ba4",
                        "authkey"  => "j8znJb83n04XeenAPuVEOxZWRKX62DWTHpFEHaRgP1WtdUR972",
                        "to"       => preg_replace('/^08/', '628', $member->phone),
                        "message"  => $message,
                    ];

                    Http::post('https://app.saungwa.com/api/create-message', $payload);
                }

                $this->info("Invoice {$invoice->invoice_number} ditandai expired & notifikasi terkirim.");
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error: " . $e->getMessage());
        }
    }
}
