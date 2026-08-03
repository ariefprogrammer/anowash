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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('owner_id')->constrained('owners')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('subscription_plans')->restrictOnDelete();
            $table->enum('status', ['trial', 'active', 'expired', 'cancelled'])->default('trial');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('current_outlet_count')->default(1);
            $table->decimal('current_price', 12, 2)->default(0);
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            $table->index(['owner_id', 'status'], 'idx_subscriptions_owner_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
