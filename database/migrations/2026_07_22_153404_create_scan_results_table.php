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
        Schema::create('scan_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained()->cascadeOnDelete();
            $table->enum('device', ['mobile', 'desktop'])->default('mobile');
            $table->unsignedTinyInteger('performance')->nullable();
            $table->unsignedTinyInteger('accessibility')->nullable();
            $table->unsignedTinyInteger('seo')->nullable();
            $table->unsignedTinyInteger('best_practices')->nullable();
            $table->unsignedInteger('fcp')->nullable();
            $table->unsignedInteger('lcp')->nullable();
            $table->decimal('cls', 5, 3)->nullable();
            $table->unsignedInteger('tbt')->nullable();
            $table->unsignedInteger('speed_index')->nullable();
            $table->unsignedInteger('tti')->nullable();
            $table->json('raw_json')->nullable();
            $table->integer('exit_code')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scan_results');
    }
};
