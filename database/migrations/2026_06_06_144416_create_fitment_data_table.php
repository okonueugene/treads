<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fitment_data', function (Blueprint $table) {
            $table->id();
            $table->string('make');
            $table->string('model');
            $table->unsignedSmallInteger('year_from');
            $table->unsignedSmallInteger('year_to');
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('aspect_ratio');
            $table->unsignedSmallInteger('rim_diameter');
            $table->string('position', 10)->default('all');
            $table->string('trim')->nullable();
            $table->timestamps();

            $table->index(['make', 'model']);
            $table->index(['year_from', 'year_to']);
            $table->index(['width', 'aspect_ratio', 'rim_diameter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fitment_data');
    }
};
