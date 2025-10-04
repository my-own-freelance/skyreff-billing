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
        Schema::create('mutations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->integer('amount')->default(0);
            $table->enum('type', ['C', 'W']); // C = commission . W = Widhraw
            $table->integer('first_commission')->default(0);
            $table->integer('last_commission')->default(0);
            $table->enum('status', ['PENDING', 'PROCESS', 'SUCCESS', 'REJECT', 'CANCEL'])->default('PENDING');
            $table->string('bank_name')->nullable(); // untuk widhraw
            $table->string('bank_account')->nullable(); // untuk widhraw
            $table->string('proof_of_payment')->nullable(); // bukti bayar oleh admin jika tipe widhraw
            $table->unsignedBigInteger('user_id'); // relasi ke tabel user role teknisi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutations');
    }
};
