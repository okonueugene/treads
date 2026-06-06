<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('condition', 10)->default('new')->after('stock');
            $table->string('condition_grade', 20)->nullable()->after('condition');
            $table->decimal('tread_depth_mm', 4, 1)->nullable()->after('condition_grade');
            $table->unsignedTinyInteger('dot_week')->nullable()->after('tread_depth_mm');
            $table->unsignedSmallInteger('dot_year')->nullable()->after('dot_week');
            $table->unsignedInteger('remaining_mileage_km')->nullable()->after('dot_year');
            $table->text('defects')->nullable()->after('remaining_mileage_km');
            $table->boolean('is_verified')->default(false)->after('defects');
            $table->unsignedInteger('sold_count')->default(0)->after('is_verified');

            $table->index(['condition', 'is_active']);
            $table->index('condition_grade');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['condition', 'is_active']);
            $table->dropIndex(['condition_grade']);
            $table->dropColumn([
                'condition',
                'condition_grade',
                'tread_depth_mm',
                'dot_week',
                'dot_year',
                'remaining_mileage_km',
                'defects',
                'is_verified',
                'sold_count',
            ]);
        });
    }
};
