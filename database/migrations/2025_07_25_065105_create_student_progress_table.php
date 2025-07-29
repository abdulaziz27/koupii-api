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
        Schema::create('student_progress', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->enum('skill_type', ['reading','listening','speaking','writing']);
            $table->integer('tasks_completed')->default(0);
            $table->integer('total_time_spent_seconds')->default(0);
            $table->decimal('average_score', 5, 2)->default(0);
            $table->json('monthly_performance')->nullable();
            $table->json('weakest_question_types')->nullable();
            $table->json('improvement_trends')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_progress');
    }
};
