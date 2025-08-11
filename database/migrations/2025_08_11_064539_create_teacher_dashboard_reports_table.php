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
        Schema::create('test_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('test_id');
            $table->uuid('class_id');
            $table->integer('total_submissions')->default(0);
            $table->decimal('average_score', 8, 2)->nullable();
            $table->decimal('highest_score', 8, 2)->nullable();
            $table->decimal('lowest_score', 8, 2)->nullable();
            $table->uuid('most_mistaken_question_id')->nullable();
            $table->timestamp('report_generated_at')->nullable();
            $table->timestamps();

            $table->foreign('test_id')->references('id')->on('tests')->cascadeOnDelete();
            $table->foreign('class_id')->references('id')->on('classes')->cascadeOnDelete();
            $table->foreign('most_mistaken_question_id')->references('id')->on('test_questions')->nullOnDelete();
        });

        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('test_report_id');
            $table->uuid('student_id');
            $table->decimal('score', 8, 2)->nullable();
            $table->enum('status', ['on_time','late','unsubmitted'])->default('on_time');
            $table->timestamp('submission_date')->nullable();
            $table->timestamps();

            $table->foreign('test_report_id')->references('id')->on('test_reports')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_reports');
        Schema::dropIfExists('leaderboard_entries');
    }
};
