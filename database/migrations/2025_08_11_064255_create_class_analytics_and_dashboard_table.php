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
        Schema::create('class_analytics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('class_id');
            $table->integer('total_students')->default(0);
            $table->decimal('average_score', 8, 2)->nullable();
            $table->decimal('highest_score', 8, 2)->nullable();
            $table->decimal('lowest_score', 8, 2)->nullable();
            $table->uuid('most_mistaken_question_id')->nullable();
            $table->timestamp('report_date')->nullable();
            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('classes')->cascadeOnDelete();
            $table->foreign('most_mistaken_question_id')->references('id')->on('test_questions')->nullOnDelete();
        });

        Schema::create('student_dashboard_metrics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('student_id');
            $table->integer('tasks_completed')->default(0);
            $table->integer('total_time_spent_seconds')->default(0);
            $table->decimal('average_score', 8, 2)->nullable();
            $table->uuid('weakest_question_type')->nullable();
            $table->uuid('best_question_type')->nullable();
            $table->json('reading_progress_by_section')->nullable();
            $table->date('metric_month')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_analytics');
        Schema::dropIfExists('student_dashboard_metrics');
    }
};
