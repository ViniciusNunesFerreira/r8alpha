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
            if (!Schema::hasColumn('investments', 'payment_method')) {
                $table->enum('payment_method', ['wallet', 'pix', 'crypto', 'bank_transfer'])->nullable()->after('status');
            }
            
            if (!Schema::hasColumn('investments', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'expired'])->default('pending')->after('payment_method');
            }
            
            if (!Schema::hasColumn('investments', 'payment_data')) {
                $table->json('payment_data')->nullable()->after('payment_status');
            }
            
            if (!Schema::hasColumn('investments', 'last_profit_at')) {
                $table->timestamp('last_profit_at')->nullable()->after('expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investments', function (Blueprint $table) {
            //
        });
    }
};
