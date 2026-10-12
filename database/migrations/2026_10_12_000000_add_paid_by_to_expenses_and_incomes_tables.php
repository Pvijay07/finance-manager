<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'paid_by')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->unsignedBigInteger('paid_by')->nullable()->after('status');
                $table->foreign('paid_by')->references('id')->on('users')->nullOnDelete();
            });

            // Backfill paid_by for historical records already marked as paid
            try {
                DB::table('expenses')
                    ->whereIn('status', ['paid', 'settle', 'settled'])
                    ->whereNull('paid_by')
                    ->whereNotNull('created_by')
                    ->update(['paid_by' => DB::raw('created_by')]);
            } catch (\Throwable $e) {
                // Ignore if backfill query cannot run
            }
        }

        if (Schema::hasTable('incomes') && !Schema::hasColumn('incomes', 'paid_by')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->unsignedBigInteger('paid_by')->nullable()->after('status');
                $table->foreign('paid_by')->references('id')->on('users')->nullOnDelete();
            });

            // Backfill paid_by for historical records already marked as received/paid
            try {
                DB::table('incomes')
                    ->whereIn('status', ['paid', 'received', 'settle', 'settled'])
                    ->whereNull('paid_by')
                    ->whereNotNull('created_by')
                    ->update(['paid_by' => DB::raw('created_by')]);
            } catch (\Throwable $e) {
                // Ignore if backfill query cannot run
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'paid_by')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropForeign(['paid_by']);
                $table->dropColumn('paid_by');
            });
        }

        if (Schema::hasTable('incomes') && Schema::hasColumn('incomes', 'paid_by')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->dropForeign(['paid_by']);
                $table->dropColumn('paid_by');
            });
        }
    }
};
