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
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_amount',10,2);
            $table->decimal('delivery_fee',10,2)->default(0.00);
            $table->text('delivery_address');
            $table->string('delivery_phone');
            $table->enum('status',['pending','confirmed','preparing','delivering','completed','cancelled'])->default('pending');
            $table->enum('payment_method',['cod','kbzpay','wavepay'])->default('cod');
            $table->enum('payment_status',['paid','unpaid'])->default('paid');
            $table->text('notes')->nullable();
            $table->timestamps();
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
