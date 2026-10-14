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
        if (Schema::hasTable('incomes') && !Schema::hasColumn('incomes', 'received_amount')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->decimal('received_amount', 15, 2)->nullable()->after('amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('incomes') && Schema::hasColumn('incomes', 'received_amount')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->dropColumn('received_amount');
            });
        }
    }
};
