<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class GenerateInvoices extends Command
{
    protected $signature = 'invoices:generate';
    protected $description = 'Generate invoices for subscriptions whose next_invoice_at has passed';

    public function handle()
    {
        $this->info('🔍 Checking subscriptions for invoice generation...');

        $subscriptions = Subscription::with(['plan', 'user'])
            ->where('status', 'active')
            ->whereNotNull('next_invoice_at')
            ->where('next_invoice_at', '<=', now())
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('✅ No subscriptions need invoicing.');
            return Command::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            DB::beginTransaction();
            try {
                // Generate nomor invoice unik
                $invoiceNumber = 'INV' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

                // Hitung jumlah dari plan
                $amount = $subscription->plan->price ?? 0;

                // Tentukan due date (7 hari setelah invoice)
                $dueDate = now()->addDays(7);

                // Buat invoice baru
                $invoice = Invoice::create([
                    'status' => 'unpaid',
                    'invoice_number' => $invoiceNumber,
                    'amount' => $amount,
                    'invoice_period_start' => $subscription->current_period_start,
                    'invoice_period_end' => $subscription->current_period_end,
                    'due_date' => $dueDate,
                    'subscription_id' => $subscription->id,
                    'plan_id' => $subscription->plan_id,
                    'user_id' => $subscription->user_id,
                    'metadata' => json_encode([
                        'plan_id' => $subscription->plan->id ?? null,
                        'plan_name' => $subscription->plan->name ?? null,
                        'plan_price' => $subscription->plan->price ?? null,
                        'subscription_id' => $subscription->id,
                        'subscription_type' => $subscription->type,
                        'subscription_username' => $subscription->username ?? null,
                        'subscription_password' => $subscription->password ?? null,
                        'subscription_queue' => $subscription->queue ?? null,
                        'user_id' => $subscription->user->id ?? null,
                        'user_name' => $subscription->user->name ?? null,
                        'user_phone' => $subscription->user->phone ?? null,
                    ]),
                ]);

                // Update next_invoice_at → 1 bulan berikutnya
                $subscription->next_invoice_at = $subscription->next_invoice_at
                    ? Carbon::parse($subscription->next_invoice_at)->addMonth()
                    : now()->addMonth();

                $subscription->save();

                DB::commit();

                // Kirim notifikasi WA
                $member = $subscription->user;
                $message = "Halo {$member->name},\n\n";
                $message .= "Invoice baru telah terbit untuk subscription Anda.\n\n";
                $message .= "*Nomor Invoice:* {$invoiceNumber}\n";
                $message .= "*Jumlah:* Rp " . number_format($amount, 0, ',', '.') . "\n";
                $message .= "*Paket :* {$subscription->plan->name}\n";

                // Format periode dengan Carbon (WIB + Indonesia)
                $message .= "*Periode:* "
                    . Carbon::parse($invoice->invoice_period_start)
                    ->timezone('Asia/Jakarta')
                    ->locale('id')
                    ->translatedFormat('d M Y')
                    . " s/d "
                    . Carbon::parse($invoice->invoice_period_end)
                    ->timezone('Asia/Jakarta')
                    ->locale('id')
                    ->translatedFormat('d M Y') . "\n";

                // Format due date dengan Carbon (WIB + Indonesia)
                $message .= "*Jatuh tempo:* "
                    . Carbon::parse($dueDate)
                    ->timezone('Asia/Jakarta')
                    ->locale('id')
                    ->translatedFormat('d M Y') . "\n\n";
                $message .= "Silakan lakukan pembayaran tepat waktu. Terima kasih.";

                $payload = [
                    "appkey" => "6879d35c-268e-4e2a-ae43-15528fc86ba4",
                    "authkey" => "j8znJb83n04XeenAPuVEOxZWRKX62DWTHpFEHaRgP1WtdUR972",
                    "to" => preg_replace('/^08/', '628', $member->phone),
                    "message" => $message,
                ];

                Http::post('https://app.saungwa.com/api/create-message', $payload);

                $this->info("✅ Invoice {$invoiceNumber} created for subscription {$subscription->id}");
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("❌ Failed to generate invoice for subscription {$subscription->id}: " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
