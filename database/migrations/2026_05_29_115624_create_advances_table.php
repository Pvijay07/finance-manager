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
        Schema::create('advances', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_type');
            $table->enum('direction', ['IN', 'OUT']);
            $table->foreignId('party_id')->constrained('parties')->onDelete('cascade');
            $table->string('party_type');
            $table->string('reference_number')->nullable()->unique();
            $table->decimal('amount', 15, 2);
            $table->decimal('recovered_amount', 15, 2)->default(0);
            $table->decimal('outstanding_amount', 15, 2);
            $table->date('transaction_date');
            $table->date('expected_recovery_date')->nullable();
            $table->string('status');
            $table->text('purpose');
            $table->text('comments')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('linked_advance_id')->nullable()->constrained('advances')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('advance_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advance_id')->constrained('advances')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->string('file_size')->nullable();
            $table->string('attachment_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advance_attachments');
        Schema::dropIfExists('advances');
    }
};
