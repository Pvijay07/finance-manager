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
        Schema::create('salary_sheet_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_sheet_id');
            $table->unsignedBigInteger('salary_employee_id');
            $table->decimal('present_days', 5, 2)->default(0);
            $table->decimal('lop_days', 5, 2)->default(0);
            $table->decimal('ot_amount', 15, 2)->default(0);
            $table->decimal('basic', 15, 2)->default(0);
            $table->decimal('hra', 15, 2)->default(0);
            $table->decimal('allowance', 15, 2)->default(0);
            $table->decimal('incentive', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('pf', 15, 2)->default(0);
            $table->decimal('esic', 15, 2)->default(0);
            $table->decimal('tds', 15, 2)->default(0);
            $table->decimal('advance_rec', 15, 2)->default(0);
            $table->decimal('other_ded', 15, 2)->default(0);
            
            // JSON column for dynamic custom components
            $table->json('custom_earnings')->nullable();
            $table->json('custom_deductions')->nullable();

            $table->decimal('gross_pay', 15, 2)->default(0);
            $table->decimal('deductions', 15, 2)->default(0);
            $table->decimal('net_pay', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('salary_sheet_id')->references('id')->on('salary_sheets')->onDelete('cascade');
            $table->foreign('salary_employee_id')->references('id')->on('salary_employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_sheet_items');
    }
};
