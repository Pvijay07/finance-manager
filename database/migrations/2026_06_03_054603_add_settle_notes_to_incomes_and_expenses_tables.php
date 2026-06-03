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
        Schema::table('incomes', function (Blueprint $table) {
            $table->text('settle_notes')->nullable()->after('notes');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->text('settle_notes')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('settle_notes');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('settle_notes');
        });
    }
};
