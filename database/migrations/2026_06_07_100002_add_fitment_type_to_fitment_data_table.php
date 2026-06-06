<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fitment_data', function (Blueprint $table) {
            $table->string('fitment_type', 10)->default('oem')->after('rim_diameter');
        });
    }

    public function down(): void
    {
        Schema::table('fitment_data', function (Blueprint $table) {
            $table->dropColumn('fitment_type');
        });
    }
};
