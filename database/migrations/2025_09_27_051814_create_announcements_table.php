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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->longText('message');
            $table->enum('type', ['P', 'I', 'S', 'W', 'D'])->default('I'); // your custom types
            $table->enum('is_active', ['Y', 'N'])->default('Y');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete(); // optional specific user
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete(); // optional specific area
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
