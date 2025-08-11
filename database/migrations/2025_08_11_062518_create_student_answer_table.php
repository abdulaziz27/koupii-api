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
        Schema::create('student_question_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_assignment_id');
            $table->uuid('question_id');
            $table->json('selected_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_earned', 8, 2)->nullable();
            $table->integer('time_spent_seconds')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('student_assignment_id')->references('id')->on('student_assignments')->cascadeOnDelete();
            $table->foreign('question_id')->references('id')->on('test_questions')->cascadeOnDelete();
        });

        Schema::create('test_results', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_assignment_id');
            $table->decimal('score', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->integer('total_correct')->nullable();
            $table->integer('total_incorrect')->nullable();
            $table->integer('total_unanswered')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('student_assignment_id')->references('id')->on('student_assignments')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_question_attempts');
        Schema::dropIfExists('test_results');
    }
};
