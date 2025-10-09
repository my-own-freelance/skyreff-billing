<?php

namespace App\Console\Commands;

use App\Helpers\BroadcastHelper;
use App\Models\BroadcastTemplate;
use App\Models\Invoice;
use App\Models\WebConfig;
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
                    // 🔔 SEND NOTIFIKASI BROADCAST TEMPLATE
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

                    $templateInvoiceBaru = BroadcastTemplate::where("code", "invoice-expired")->where('is_active', 'Y')->first();
                    if ($templateInvoiceBaru) {
                        $appConfig = WebConfig::first();
                        // Mapping data untuk parsing
                        $dataTemplate = [
                            'member_name'     => $member->name,
                            'invoice_number'  => $invoice->invoice_number,
                            'plan_name'       => $invoice->plan->name,
                            'invoice_amount'  => "Rp " . number_format($invoice->amount, 0, ',', '.'),
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
