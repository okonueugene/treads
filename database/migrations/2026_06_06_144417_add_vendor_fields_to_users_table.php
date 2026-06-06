<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_vendor')->default(false)->after('email');
            $table->string('shop_name')->nullable()->after('is_vendor');
            $table->decimal('commission_rate', 5, 2)->default(10.00)->after('shop_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_vendor', 'shop_name', 'commission_rate']);
        });
    }
};
