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
        if (Schema::hasTable('incomes') && !Schema::hasColumn('incomes', 'settle_notes')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->text('settle_notes')->nullable()->after('notes');
            });
        }
        if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'settle_notes')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->text('settle_notes')->nullable()->after('notes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('incomes') && Schema::hasColumn('incomes', 'settle_notes')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->dropColumn('settle_notes');
            });
        }
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'settle_notes')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('settle_notes');
            });
        }
    }
};
