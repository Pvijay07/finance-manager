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
        DB::statement("ALTER TABLE upcoming_payments MODIFY COLUMN source ENUM('standard', 'income', 'non_standard', 'salary_module') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE upcoming_payments MODIFY COLUMN source ENUM('standard', 'income', 'non_standard') NOT NULL");
    }
};
