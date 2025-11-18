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
        Schema::create('bot_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('investment_id')->constrained()->onDelete('cascade');
            $table->string('instance_id')->unique();
            $table->boolean('is_active')->default(false);
            $table->json('config')->nullable();
            $table->integer('total_trades')->default(0);
            $table->integer('successful_trades')->default(0);
            $table->decimal('total_profit', 20, 8)->default(0);
            $table->timestamp('last_trade_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_instances');
    }
};
