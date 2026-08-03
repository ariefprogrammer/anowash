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
        Schema::create('marketing_commissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('marketing_id');
            $table->uuid('owner_id');
            $table->foreignId('subscription_invoice_id')->unique();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['unpaid', 'paid'])->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('marketing_id', 'fk_mkt_comm_marketing')->references('id')->on('marketings')->cascadeOnDelete();
            $table->foreign('owner_id', 'fk_mkt_comm_owner')->references('id')->on('owners')->cascadeOnDelete();
            $table->foreign('subscription_invoice_id', 'fk_mkt_comm_invoice')->references('id')->on('subscription_invoices')->cascadeOnDelete();
            $table->index(['marketing_id', 'status'], 'idx_mkt_comm_marketing_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_commissions');
    }
};
