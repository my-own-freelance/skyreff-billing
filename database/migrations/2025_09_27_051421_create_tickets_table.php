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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['gangguan', 'maintenance', 'pemasangan', 'troubleshoot', 'lain-lain'])->default('gangguan');
            $table->enum('status', ['open', 'inprogress', 'success', 'reject', 'failed'])->default('open');
            $table->text('cases')->nullable();
            $table->text('solution')->nullable();
            $table->foreignId('member_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete(); // who created ticket (admin/user)
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('complaint_image')->nullable(); // optional single image path
            $table->string('completion_image')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
