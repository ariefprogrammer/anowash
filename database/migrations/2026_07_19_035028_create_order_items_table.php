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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id');
            $table->foreignId('service_id');
            $table->string('service_name_snapshot', 150);
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();

            $table->foreign('order_id', 'fk_order_items_order')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('service_id', 'fk_order_items_service')->references('id')->on('services')->restrictOnDelete();
            $table->index('order_id', 'idx_order_items_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
