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
        Schema::table('deposits', function (Blueprint $table) {
            // Adiciona campos para identificar tipo de pagamento
            $table->enum('payment_type', ['deposit', 'investment'])
                ->default('deposit')
                ->after('status')
                ->comment('Tipo do pagamento: depósito direto ou checkout de investimento');
            
            // Adiciona referência para investimento (quando for checkout)
            $table->unsignedBigInteger('reference_id')
                ->nullable()
                ->after('payment_type')
                ->comment('ID do investment quando payment_type = investment');
            
            // Índices para performance
            $table->index(['payment_type', 'status']);
            $table->index('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropIndex(['payment_type', 'status']);
            $table->dropIndex(['reference_id']);
            $table->dropColumn(['payment_type', 'reference_id']);
        });
    }
};