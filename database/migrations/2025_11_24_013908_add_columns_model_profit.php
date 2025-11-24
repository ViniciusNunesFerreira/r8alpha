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
        Schema::table('profits', function (Blueprint $table) {
            
            // Relacionamentos
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->comment('Usuário dono do investimento')->after('id');

            $table->foreignId('investment_id')
                  ->constrained('investments')
                  ->onDelete('cascade')
                  ->comment('Investimento que gerou este lucro')->after('user_id');

            // Valores Financeiros
            // Usamos decimal(20, 8) para alta precisão em cripto, ou (10, 2) para moeda fiat simples.
            // Recomendado sobrar casas decimais para evitar erros de arredondamento.
            $table->decimal('amount', 20, 8)->comment('Valor bruto do lucro gerado no dia')->after('investment_id');

            // Data de referência do lucro (para evitar duplicidade no mesmo dia)
            $table->timestamp('date')->comment('Data/Hora de referência do pagamento')->after('amount');
            
            // Índices para performance em relatórios
            $table->index(['user_id', 'created_at']);
            $table->index(['investment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profits', function (Blueprint $table) {
            //
        });
    }
};
