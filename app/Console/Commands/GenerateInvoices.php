<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateInvoices extends Command
{
    protected $signature = 'invoice:generate-invoices';
    protected $description = 'Generate invoices for active subscriptions';

    public function handle()
    {
        $today = Carbon::today();

        // Ambil subscription yang perlu dibuatkan invoice
        $subscriptions = Subscription::with('plan')->where('status', 'active')->get();

        foreach ($subscriptions as $subscription) {
            // Cek apakah invoice bulan ini sudah ada
            $exists = Invoice::where('subscription_id', $subscription->id)
                ->whereMonth('due_date', $today->month)
                ->whereYear('due_date', $today->year)
                ->exists();

            if (!$exists) {
                $plan = $subscription->plan;

                Invoice::create([
                    'subscription_id' => $subscription->id,
                    'amount' => $subscription->price, // atau ambil dari plan
                    'status' => 'unpaid',
                    'due_date' => $today->copy()->addDays(7), // jatuh tempo 7 hari
                    'metadata' => [
                        'plan_id'       => $plan->id,
                        'plan_name'     => $plan->name,
                        'price'         => $plan->price,
                        'level'         => $plan->level,
                        'description'   => $plan->description ?? null,
                        'features'   => $plan->features ?? null,
                    ]
                ]);

                $this->info("Invoice created for subscription #{$subscription->id}");
            }
        }
    }
}
