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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('CHF');

            $table->enum('payment_method', ['credit_card', 'debit_card', 'paypal', 'twint', 'cash'])
                  ->default('credit_card');
            $table->enum('provider_name', ['stripe', 'twint', 'postfinance', 'cash'])
                  ->default('stripe');
            $table->string('provider_transaction_id')->nullable();

            $table->enum('status', ['PENDING', 'PROCESSING', 'COMPLETED', 'FAILED', 'REFUNDED', 'PARTIAL_REFUND', 'CANCELLED', 'EXPIRED'])
                  ->default('PENDING');

            $table->string('stripe_payment_intent_id')->nullable();
            $table->text('stripe_client_secret')->nullable();

            // Refund tracking
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->string('refund_reason')->nullable();
            $table->string('refund_id')->nullable();
            $table->timestamp('refunded_at')->nullable();

            // Audit
            $table->text('error_message')->nullable();
            $table->string('error_code')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('ip_address', 45)->nullable();

            $table->timestamps();

            $table->index('order_id');
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
