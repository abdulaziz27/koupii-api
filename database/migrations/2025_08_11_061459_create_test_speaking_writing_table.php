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
        Schema::create('speaking_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('test_id');
            $table->enum('section_type', ['introduction', 'long_turn', 'discussion']);
            $table->text('description')->nullable();
            $table->integer('prep_time_seconds')->nullable();
            $table->timestamps();

            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
        });

        Schema::create('speaking_topics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('speaking_section_id');
            $table->string('topic_name');
            $table->timestamps();

            $table->foreign('speaking_section_id')->references('id')->on('speaking_sections')->onDelete('cascade');
        });

        Schema::create('speaking_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('speaking_topic_id');
            $table->integer('question_number');
            $table->text('question_text');
            $table->integer('time_limit_seconds');
            $table->timestamps();

            $table->foreign('speaking_topic_id')->references('id')->on('speaking_topics')->onDelete('cascade');
        });

        Schema::create('writing_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('test_id');
            $table->enum('task_type', ['report', 'essay']);
            $table->text('topic')->nullable();
            $table->text('prompt')->nullable();
            $table->integer('suggest_time_minutes')->nullable();
            $table->integer('min_word_count')->nullable();
            $table->text('sample_answer')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();

            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('writing_tasks');
        Schema::dropIfExists('speaking_questions');
        Schema::dropIfExists('speaking_topics');
        Schema::dropIfExists('speaking_sections');
    }
};
