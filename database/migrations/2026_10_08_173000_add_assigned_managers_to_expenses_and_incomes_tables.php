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
        if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'assigned_managers')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->json('assigned_managers')->nullable()->after('notes');
            });
        }

        if (Schema::hasTable('incomes') && !Schema::hasColumn('incomes', 'assigned_managers')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->json('assigned_managers')->nullable()->after('notes');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'assigned_managers')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('assigned_managers');
            });
        }

        if (Schema::hasTable('incomes') && Schema::hasColumn('incomes', 'assigned_managers')) {
            Schema::table('incomes', function (Blueprint $table) {
                $table->dropColumn('assigned_managers');
            });
        }
    }
};
