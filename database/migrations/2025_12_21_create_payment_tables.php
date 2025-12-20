<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Create tables untuk payment system:
     * - payments: Main payment transaction records
     * - invoices: Invoice untuk payment tracking
     * - invoice_items: Line items dalam invoice
     * - tax_records: Tax compliance tracking
     */
    public function up(): void
    {
        // Payments table
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('consultation_id')->nullable()->constrained('konsultasis')->onDelete('set null');
                $table->foreignId('emergency_id')->nullable()->constrained('emergencies')->onDelete('set null');
                
                // Amount details
                $table->decimal('amount', 12, 2);
                $table->string('currency')->default('IDR');
                
                // Payment method: stripe, gcash, bank_transfer, e_wallet
                $table->enum('payment_method', ['stripe', 'gcash', 'bank_transfer', 'e_wallet'])->nullable();
                
                // Status: pending, processing, completed, failed, refunded
                $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
                
                // Gateway info
                $table->string('transaction_id')->nullable()->unique();
                $table->text('receipt_url')->nullable();
                $table->json('gateway_response')->nullable();
                $table->text('failed_reason')->nullable();
                
                // Refund info
                $table->timestamp('refunded_at')->nullable();
                $table->decimal('refund_amount', 12, 2)->nullable();
                
                // Additional
                $table->text('notes')->nullable();
                
                $table->softDeletes();
                $table->timestamps();
                
                // Indexes
                $table->index('user_id');
                $table->index('status');
                $table->index('payment_method');
                $table->index('created_at');
            });
        }

        // Invoices table
        if (!Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                
                // Invoice info
                $table->string('invoice_number')->unique();
                $table->date('invoice_date');
                $table->date('due_date');
                
                // Amount breakdown
                $table->decimal('subtotal', 12, 2);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2);
                
                // Status: draft, sent, overdue, paid, cancelled
                $table->enum('status', ['draft', 'sent', 'overdue', 'paid', 'cancelled'])->default('draft');
                
                // Timestamps
                $table->timestamp('sent_at')->nullable();
                $table->timestamp('paid_at')->nullable();
                
                // Notes
                $table->text('notes')->nullable();
                
                $table->softDeletes();
                $table->timestamps();
                
                // Indexes
                $table->index('invoice_number');
                $table->index('user_id');
                $table->index('status');
                $table->index('invoice_date');
            });
        }

        // Invoice items table
        if (!Schema::hasTable('invoice_items')) {
            Schema::create('invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
                
                // Item details
                $table->string('description');
                $table->enum('item_type', ['consultation', 'emergency', 'service', 'discount'])->default('service');
                
                // Amount calculation
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 12, 2);
                $table->decimal('amount', 12, 2);
                
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // Tax records table (immutable)
        if (!Schema::hasTable('tax_records')) {
            Schema::create('tax_records', function (Blueprint $table) {
                $table->id();
                $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
                
                // Tax info
                $table->enum('tax_type', ['pph', 'ppn', 'other'])->index();
                $table->decimal('tax_rate', 5, 2);
                $table->decimal('base_amount', 12, 2);
                $table->decimal('tax_amount', 12, 2);
                
                // Status: calculated, reported
                $table->enum('status', ['calculated', 'reported'])->default('calculated')->index();
                
                // Timestamps
                $table->timestamp('calculated_at')->useCurrent();
                $table->timestamp('reported_at')->nullable();
                
                // Notes
                $table->text('notes')->nullable();
                
                // Immutable - no updated_at
                
                // Indexes
                $table->index(['payment_id', 'tax_type']);
                $table->index('calculated_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_records');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
    }
};
