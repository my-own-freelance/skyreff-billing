<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiredInvoices extends Command
{
    protected $signature = 'invoice:check-expired-invoices';
    protected $description = 'Mark expired invoices as expired';

    public function handle()
    {
        $today = Carbon::today();

        $invoices = Invoice::where('status', 'unpaid')
            ->whereDate('due_date', '<', $today)
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->update(['status' => 'expired']);
            $this->info("Invoice #{$invoice->id} marked as expired");
        }
    }
}
