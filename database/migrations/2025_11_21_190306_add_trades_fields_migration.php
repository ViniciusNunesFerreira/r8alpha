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
        Schema::table('trades', function (Blueprint $table) {
            if (!Schema::hasColumn('trades', 'profit')) {
                $table->decimal('profit', 20, 8)->nullable()->after('total');
            }

            if (!Schema::hasColumn('trades', 'exchange_order_id')) {
                $table->string('exchange_order_id')->nullable()->after('status');
            }

            if (!Schema::hasColumn('trades', 'executed_at')) {
                $table->timestamp('executed_at')->nullable()->after('fees');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            //
        });
    }
};
