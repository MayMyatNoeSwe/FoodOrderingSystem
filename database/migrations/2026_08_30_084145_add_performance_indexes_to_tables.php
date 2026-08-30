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
        Schema::table('shops', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->index('is_available');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_status']);
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropIndex(['is_available']);
        });

        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });
    }
};
