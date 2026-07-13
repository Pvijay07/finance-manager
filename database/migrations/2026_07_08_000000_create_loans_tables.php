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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('party_id')->constrained()->cascadeOnDelete();
            
            // Basic Details
            $table->string('loan_number')->unique(); // LT-2026-0001, LG-2026-0001
            $table->enum('direction', ['taken', 'given']);
            $table->string('loan_name')->nullable();
            $table->string('loan_category')->nullable();
            
            // Financial Details
            $table->decimal('principal_amount', 15, 2);
            $table->date('loan_date');
            $table->date('disbursement_date')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('loan_period')->nullable(); // in months
            $table->string('currency')->default('INR');
            
            // Interest Details
            $table->boolean('interest_applicable')->default(false);
            $table->enum('interest_type', ['flat', 'reducing', 'compound'])->nullable();
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->enum('interest_frequency', ['monthly', 'quarterly', 'half-yearly', 'yearly'])->nullable();
            $table->date('interest_start_date')->nullable();
            
            // EMI Details
            $table->boolean('enable_emi')->default(false);
            $table->decimal('emi_amount', 15, 2)->nullable();
            $table->integer('emi_count')->nullable();
            $table->date('emi_start_date')->nullable();
            $table->integer('emi_due_day')->nullable(); // 1-31
            
            // Processing Charges
            $table->decimal('processing_fee', 15, 2)->default(0);
            $table->decimal('documentation_fee', 15, 2)->default(0);
            $table->decimal('legal_charges', 15, 2)->default(0);
            $table->decimal('other_charges', 15, 2)->default(0);
            
            // Tax Details
            $table->boolean('gst_applicable')->default(false);
            $table->decimal('gst_percent', 5, 2)->nullable();
            $table->decimal('gst_amount', 15, 2)->default(0);
            $table->enum('gst_type', ['inclusive', 'exclusive'])->nullable();
            
            $table->boolean('tds_applicable')->default(false);
            $table->string('tds_section')->nullable(); // 194A, Other
            $table->decimal('tds_percent', 5, 2)->nullable();
            $table->decimal('tds_amount', 15, 2)->default(0);
            
            // Status
            $table->enum('status', ['draft', 'active', 'partial_paid', 'overdue', 'closed', 'cancelled', 'defaulted'])->default('active');
            
            // Security Details
            $table->boolean('collateral_applicable')->default(false);
            $table->enum('collateral_type', ['property', 'vehicle', 'gold', 'fd', 'documents', 'others'])->nullable();
            
            // Guarantor Details
            $table->string('guarantor_name')->nullable();
            $table->string('guarantor_mobile')->nullable();
            $table->text('guarantor_address')->nullable();
            $table->string('guarantor_relation')->nullable();
            
            $table->text('purpose')->nullable();
            $table->text('comments')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('loan_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            
            $table->enum('type', ['disbursement', 'payment', 'interest_accrual', 'penalty', 'charge']);
            $table->date('transaction_date');
            $table->decimal('total_amount', 15, 2);
            
            // Breakdowns for payments
            $table->decimal('principal_component', 15, 2)->default(0);
            $table->decimal('interest_component', 15, 2)->default(0);
            $table->decimal('penalty_component', 15, 2)->default(0);
            $table->decimal('gst_component', 15, 2)->default(0);
            $table->decimal('tds_component', 15, 2)->default(0);
            
            $table->enum('payment_mode', ['cash', 'bank', 'upi', 'cheque', 'online'])->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('loan_amortization_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->integer('installment_number');
            $table->date('due_date');
            $table->decimal('emi_amount', 15, 2);
            $table->decimal('principal_component', 15, 2);
            $table->decimal('interest_component', 15, 2);
            $table->decimal('remaining_balance', 15, 2);
            $table->enum('status', ['pending', 'paid', 'partial', 'overdue'])->default('pending');
            $table->timestamps();
        });

        Schema::create('loan_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', ['agreement', 'promissory_note', 'cheque_copy', 'kyc', 'pan', 'gst_certificate', 'mortgage_papers', 'receipt', 'others']);
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->string('file_size')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_documents');
        Schema::dropIfExists('loan_amortization_schedules');
        Schema::dropIfExists('loan_transactions');
        Schema::dropIfExists('loans');
    }
};
