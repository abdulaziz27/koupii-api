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
        Schema::create('test_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('session_id');
            $table->uuid('user_id');
            $table->uuid('test_id');
            $table->enum('test_type', ['reading', 'listening', 'speaking', 'writing']);
            $table->decimal('total_score', 8, 2)->default(0);
            $table->decimal('percentage_score', 5, 2)->default(0);
            $table->integer('questions_correct')->default(0);
            $table->integer('questions_incorrect')->default(0);
            $table->integer('questions_missed')->default(0);
            $table->json('detailed_breakdown')->nullable();
            $table->json('performance_analytics')->nullable();
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('test_sessions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};
