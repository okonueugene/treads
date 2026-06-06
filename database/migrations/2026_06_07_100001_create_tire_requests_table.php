<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tire_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone', 20);
            $table->unsignedSmallInteger('width');
            $table->unsignedSmallInteger('aspect_ratio');
            $table->unsignedSmallInteger('rim_diameter');
            $table->string('make', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('preference', 10)->default('either');
            $table->string('status', 20)->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['width', 'aspect_ratio', 'rim_diameter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tire_requests');
    }
};
