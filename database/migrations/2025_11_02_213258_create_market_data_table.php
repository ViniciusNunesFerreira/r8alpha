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
        Schema::create('market_data', function (Blueprint $table) {
            $table->id();$table->string('symbol');
            $table->decimal('price', 20, 8);
            $table->decimal('bid', 20, 8);
            $table->decimal('ask', 20, 8);
            $table->decimal('volume', 20, 8);
            $table->timestamp('timestamp');
            $table->timestamps();
            $table->index(['symbol', 'timestamp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_data');
    }
};
