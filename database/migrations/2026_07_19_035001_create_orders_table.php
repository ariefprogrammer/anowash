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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('outlet_id');
            $table->foreignId('vehicle_id')->nullable();
            $table->string('plate_number_snapshot', 20);
            $table->foreignId('vehicle_category_id');
            $table->string('customer_name_snapshot', 150)->nullable();
            $table->string('customer_phone_snapshot', 30)->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'paid', 'cancelled'])->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('payment_method', 50)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('outlet_id', 'fk_orders_outlet')->references('id')->on('outlets')->cascadeOnDelete();
            $table->foreign('vehicle_id', 'fk_orders_vehicle')->references('id')->on('vehicles')->nullOnDelete();
            $table->foreign('vehicle_category_id', 'fk_orders_category')->references('id')->on('vehicle_categories')->restrictOnDelete();
            $table->foreign('created_by', 'fk_orders_creator')->references('id')->on('users')->nullOnDelete();
            $table->index(['outlet_id', 'status'], 'idx_orders_outlet_status');
            $table->index('created_at', 'idx_orders_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
