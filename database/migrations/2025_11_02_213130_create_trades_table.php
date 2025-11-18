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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_instance_id')->constrained()->onDelete('cascade');
            $table->foreignId('arbitrage_opportunity_id')->constrained()->onDelete('cascade');
            $table->string('trade_sequence'); // step_1, step_2, step_3
            $table->string('pair');
            $table->enum('side', ['buy', 'sell']);
            $table->decimal('amount', 20, 8);
            $table->decimal('price', 20, 8);
            $table->decimal('total', 20, 8);
            $table->decimal('fee', 20, 8)->default(0);
            $table->enum('status', ['simulated', 'pending', 'completed', 'failed'])->default('simulated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
