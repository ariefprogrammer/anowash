<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('marketing_id')->nullable()->after('outlet_id');
            $table->foreign('marketing_id', 'fk_users_marketing')->references('id')->on('marketings')->cascadeOnDelete();
            $table->index('marketing_id', 'idx_users_marketing');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('fk_users_marketing');
            $table->dropColumn('marketing_id');
        });
    }
};
