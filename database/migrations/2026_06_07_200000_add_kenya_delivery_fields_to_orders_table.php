<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_method', 20)->default('home_delivery')->after('status');
            $table->string('shipping_county', 100)->nullable()->after('shipping_address');
            $table->string('shipping_town', 100)->nullable()->after('shipping_county');
            $table->string('shipping_landmark', 255)->nullable()->after('shipping_town');
            $table->timestamp('delivered_at')->nullable()->after('notes');
            $table->timestamp('receipt_confirmed_at')->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_method',
                'shipping_county',
                'shipping_town',
                'shipping_landmark',
                'delivered_at',
                'receipt_confirmed_at',
            ]);
        });
    }
};
