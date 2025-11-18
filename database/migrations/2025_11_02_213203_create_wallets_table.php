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
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('balance', 20, 8)->default(0);
            $table->enum('type', ['deposit', 'referral', 'investment', 'bonus'])->default('deposit');
            $table->decimal('total_deposited', 20, 8)->default(0);
            $table->decimal('total_withdrawn', 20, 8)->default(0);
            $table->decimal('total_profit', 20, 8)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
