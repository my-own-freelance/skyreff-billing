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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['active', 'isolir'])->default('active');
            $table->enum('type', ['pppoe', 'hotspot', 'static'])->default('pppoe');
            $table->string('username')->nullable(); // PPPoE username
            $table->string('password')->nullable(); // encrypt if needed
            $table->string('queue')->nullable(); // STATIC queue
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('next_invoice_at')->nullable();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'next_invoice_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
