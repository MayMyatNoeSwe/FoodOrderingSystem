<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_screenshot')->nullable()->after('payment_method');
            $table->string('delivery_township')->nullable()->after('delivery_address');
            $table->string('region_type')->default('yangon')->after('delivery_address');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_screenshot', 'delivery_township', 'region_type']);
        });
    }
};
