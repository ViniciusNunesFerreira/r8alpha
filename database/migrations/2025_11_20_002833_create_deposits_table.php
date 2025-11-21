<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Identificadores
            $table->string('transaction_id')->unique(); // ID único gerado pelo sistema
            $table->string('gateway_transaction_id')->nullable()->index(); // ID do gateway
            $table->string('payment_method'); // 'pix' ou 'crypto'
            $table->string('gateway'); // 'startcash' ou 'nowpayments'
            
            // Valores em USD (moeda padrão)
            $table->decimal('amount_usd', 15, 2); // Valor em USD
            
            // Valores para PIX (se aplicável)
            $table->decimal('amount_brl', 15, 2)->nullable(); // Valor em BRL
            $table->decimal('conversion_rate', 10, 4)->nullable(); // Taxa de conversão USD->BRL
            
            // Valores para Crypto (se aplicável)
            $table->decimal('amount_crypto', 20, 8)->nullable(); // Valor em USDT
            $table->string('crypto_currency')->nullable(); // 'USDT'
            $table->string('crypto_network')->nullable(); // 'BEP20'
            $table->string('crypto_address')->nullable(); // Endereço para depósito
            
            // Dados de pagamento
            $table->text('payment_data')->nullable(); // JSON com dados do QR Code, endereço, etc
            $table->text('qr_code_image')->nullable(); // Base64 ou URL do QR Code
            $table->string('pix_code')->nullable(); // Código PIX copia e cola
            
            // Status e controle
            $table->string('status')->default('pending')->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            
            // Webhook e notificações
            $table->text('webhook_data')->nullable(); // Dados recebidos via webhook
            $table->integer('webhook_attempts')->default(0);
            $table->timestamp('last_webhook_at')->nullable();
            
            // Metadados
            $table->string('user_ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['user_id', 'status']);
            $table->index(['payment_method', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};