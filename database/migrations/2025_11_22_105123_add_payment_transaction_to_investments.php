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
        Schema::table('investments', function (Blueprint $table) {
            // Adiciona referência para a transação de pagamento
            $table->string('payment_transaction_id', 100)
                ->nullable()
                ->after('payment_status')
                ->comment('ID da transação no deposits (transaction_id)');
            
            // Índice para buscar rapidamente
            $table->index('payment_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            $table->dropIndex(['payment_transaction_id']);
            $table->dropColumn('payment_transaction_id');
        });
    }
};