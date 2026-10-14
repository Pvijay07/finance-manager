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
        if (Schema::hasTable('incomes')) {
            Schema::table('incomes', function (Blueprint $table) {
                if (Schema::hasColumn('incomes', 'actual_amount')) {
                    $table->decimal('actual_amount', 15, 2)->change();
                }
                if (Schema::hasColumn('incomes', 'balance_amount')) {
                    $table->decimal('balance_amount', 15, 2)->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->decimal('actual_amount', 10, 0)->change();
            $table->decimal('balance_amount', 10, 0)->change();
        });
    }
};
