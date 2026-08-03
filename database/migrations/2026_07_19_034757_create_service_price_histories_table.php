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
        Schema::create('service_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_price_id');
            $table->decimal('old_price', 12, 2);
            $table->decimal('new_price', 12, 2);
            $table->uuid('changed_by')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('service_price_id', 'fk_price_history_price')->references('id')->on('service_prices')->cascadeOnDelete();
            $table->foreign('changed_by', 'fk_price_history_user')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_price_histories');
    }
};
