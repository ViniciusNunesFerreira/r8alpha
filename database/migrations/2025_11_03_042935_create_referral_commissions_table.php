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
        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users') 
                  ->onDelete('cascade');
            $table->foreignId('source_user_id')
                  ->constrained('users') 
                  ->onDelete('cascade');
            // 3. Relacionamento Polimórfico (Source: Investment ou Profit)
            // Cria as colunas 'source_id' (bigInteger) e 'source_type' (string)
            $table->morphs('source');

            $table->integer('level')->comment('Nível da rede de 1 a 5');;
            $table->decimal('amount', 10, 2)->default(0); 
            $table->string('type', 50)->nullable(); 

            $table->timestamps(); 

            // Índices para otimizar buscas
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
    }
};
