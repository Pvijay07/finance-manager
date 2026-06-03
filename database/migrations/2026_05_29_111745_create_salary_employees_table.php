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
        Schema::create('salary_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('emp_id')->nullable();
            $table->string('status')->default('Active'); // Active, Inactive
            $table->string('full_name');
            $table->string('department')->nullable();
            $table->string('role')->nullable();
            $table->string('salary_type')->default('Monthly'); // Monthly, Daily, Hourly
            $table->decimal('monthly_ctc', 15, 2)->default(0);
            $table->string('bank_account')->nullable();
            $table->string('pan')->nullable();
            $table->string('uan')->nullable();
            $table->string('esic')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_employees');
    }
};
