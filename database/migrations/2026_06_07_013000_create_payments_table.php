<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->enum('method', ['mpesa_express', 'mpesa_manual', 'bank_transfer', 'stripe'])->default('mpesa_express');
            $table->decimal('amount', 12, 2);
            $table->string('phone_number')->nullable();
            $table->string('transaction_code')->nullable();
            $table->enum('status', ['pending','initiated','processing','paid','failed','cancelled','refunded'])->default('pending');
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
