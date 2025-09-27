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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('price')->default(0); // in smallest currency unit, e.g. cents / rupiah
            $table->unsignedTinyInteger('level')->default(1); // 1..5
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->json('features')->nullable();
            $table->enum('is_active', ['Y', 'N'])->default('Y');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
