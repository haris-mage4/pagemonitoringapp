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
        Schema::create('uptime_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['online', 'offline', 'unavailable']);
            $table->unsignedSmallInteger('http_code')->nullable();
            $table->unsignedInteger('response_time_ms')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->index(['website_id', 'checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uptime_checks');
    }
};
