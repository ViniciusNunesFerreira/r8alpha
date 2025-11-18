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
        Schema::create('arbitrage_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_instance_id')->constrained()->onDelete('cascade');
            $table->string('base_currency');
            $table->string('quote_currency');
            $table->string('intermediate_currency');
            $table->decimal('profit_percentage', 10, 6);
            $table->decimal('estimated_profit', 20, 8);
            $table->json('prices');
            $table->enum('status', ['detected', 'executed', 'expired'])->default('detected');
            $table->timestamp('detected_at');
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arbitrage_opportunities');
    }
};
