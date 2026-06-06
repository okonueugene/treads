<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('vendor_payout', 12, 2)->nullable()->after('commission_amount');
            $table->json('commission_snapshot')->nullable()->after('vendor_payout');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['vendor_payout', 'commission_snapshot']);
        });
    }
};
