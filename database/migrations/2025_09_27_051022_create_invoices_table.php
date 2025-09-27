<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['unpaid', 'paid', 'expired'])->default('unpaid');
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamp('invoice_period_start')->nullable();
            $table->timestamp('invoice_period_end')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('metadata')->nullable(); // snapshot detail paket, etc
            $table->timestamp('paid_at')->nullable();
            $table->json('payment_data')->nullable(); // store how paid (manual/admin)
            $table->timestamps();

            $table->index(['status', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
