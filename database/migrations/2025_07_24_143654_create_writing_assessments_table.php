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
        Schema::create('writing_assessments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('session_id');
            $table->uuid('user_id');
            $table->decimal('grammatical_range_accuracy_score', 5, 2)->nullable();
            $table->decimal('lexical_resource_score', 5, 2)->nullable();
            $table->decimal('coherence_cohesion_score', 5, 2)->nullable();
            $table->decimal('task_response_score', 5, 2)->nullable();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->json('detailed_feedback')->nullable();
            $table->json('error_analysis')->nullable();
            $table->text('improvement_suggestions')->nullable();
            $table->text('suggested_revision')->nullable();
            $table->timestamp('assessed_at')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('test_sessions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('writing_assessments');
    }
};
