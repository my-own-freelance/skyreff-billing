<?php

namespace App\Console\Commands;

use App\Helpers\BroadcastHelper;
use App\Models\BroadcastTemplate;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\WebConfig;
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

                // Tentukan due date (3 hari setelah invoice keluar)
                $dueDate = now()->addDays(3);

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

                // 🔔 SEND NOTIFIKASI BROADCAST TEMPLATE
                $member = $subscription->user;
                $periodStart = Carbon::parse($invoice->invoice_period_start)
                    ->timezone('Asia/Jakarta') // atur timezone ke WIB
                    ->locale('id') // bahasa Indonesia
                    ->translatedFormat('d M Y');
                $periodEnd = Carbon::parse($invoice->invoice_period_end)
                    ->timezone('Asia/Jakarta') // atur timezone ke WIB
                    ->locale('id') // bahasa Indonesia
                    ->translatedFormat('d M Y');
                $dueDate = Carbon::parse($invoice->due_date)
                    ->timezone('Asia/Jakarta') // atur timezone ke WIB
                    ->locale('id') // bahasa Indonesia
                    ->translatedFormat('d M Y');

                $templateInvoiceBaru = BroadcastTemplate::where("code", "invoice-baru")->where('is_active', 'Y')->first();
                if ($templateInvoiceBaru) {
                    $appConfig = WebConfig::first();
                    // Mapping data untuk parsing
                    $dataTemplate = [
                        'member_name'     => $member->name,
                        'invoice_number'  => $invoiceNumber,
                        'plan_name'       => $subscription->plan->name,
                        'invoice_amount'  => "Rp " . number_format($amount, 0, ',', '.'),
                        'period'          => "{$periodStart} s/d {$periodEnd}",
                        'invoice_due_date' => $dueDate,
                        'support_contact' => 'wa.me/' . preg_replace('/^08/', '628', $appConfig->phone_number),
                        'company_name'    => $appConfig->web_title, // ganti sesuai nama perusahaan
                    ];

                    // Parsing template
                    $message = BroadcastHelper::parseTemplate($templateInvoiceBaru->content, $dataTemplate);

                    // Kirim broadcast WA
                    BroadcastHelper::send($member->phone, $message);
                }

                $this->info("✅ Invoice {$invoiceNumber} created for subscription {$subscription->id}");
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("❌ Failed to generate invoice for subscription {$subscription->id}: " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
