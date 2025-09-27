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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'teknisi', 'member'])->default('member');
            $table->string('phone')->unique();
            $table->text('address')->nullable();
            $table->string('link_maps')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('area_id')->nullable()->constrained('areas')->onDelete('set null');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
