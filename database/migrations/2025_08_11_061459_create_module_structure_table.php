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
        Schema::create('reading_passages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('test_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->timestamps();

            $table->foreign('test_id')->references('id')->on('tests')->cascadeOnDelete();
        });

        Schema::create('reading_question_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('passage_id');
            $table->uuid('question_type_id');
            $table->text('instruction')->nullable();
            $table->timestamps();

            $table->foreign('passage_id')->references('id')->on('reading_passages')->cascadeOnDelete();
            $table->foreign('question_type_id')->references('id')->on('question_types')->cascadeOnDelete();
        });

        Schema::create('listening_passages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('test_id');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->string('audio_file_path')->nullable();
            $table->enum('transcript_type', ['descriptive','dialog'])->nullable();
            $table->timestamps();

            $table->foreign('test_id')->references('id')->on('tests')->cascadeOnDelete();
        });

        Schema::create('listening_transcripts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('passage_id');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->timestamps();

            $table->foreign('passage_id')->references('id')->on('listening_passages')->cascadeOnDelete();
        });

        Schema::create('listening_dialogs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('passage_id');
            $table->string('conversation_title')->nullable();
            $table->string('speaker_name')->nullable();
            $table->text('speech_content')->nullable();
            $table->integer('sequence_number')->nullable();
            $table->timestamps();

            $table->foreign('passage_id')->references('id')->on('listening_passages')->cascadeOnDelete();
        });

        Schema::create('listening_question_groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('passage_id');
            $table->uuid('question_type_id');
            $table->text('instruction')->nullable();
            $table->timestamps();

            $table->foreign('passage_id')->references('id')->on('listening_passages')->cascadeOnDelete();
            $table->foreign('question_type_id')->references('id')->on('question_types')->cascadeOnDelete();
        });

        Schema::create('speaking_sections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('test_id');
            $table->enum('section_type', ['introduction','long_turn','discussion']);
            $table->text('description')->nullable();
            $table->integer('time_limit_seconds')->nullable();
            $table->integer('prep_time_seconds')->nullable();
            $table->timestamps();

            $table->foreign('test_id')->references('id')->on('tests')->cascadeOnDelete();
        });

        Schema::create('writing_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('test_id');
            $table->enum('task_type', ['report','essay']);
            $table->text('topic')->nullable();
            $table->text('prompt')->nullable();
            $table->integer('suggest_time_minutes')->nullable();
            $table->integer('min_word_count')->nullable();
            $table->text('sample_answer')->nullable();
            $table->timestamps();

            $table->foreign('test_id')->references('id')->on('tests')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_passages');
        Schema::dropIfExists('reading_question_groups');
        Schema::dropIfExists('listening_passages');
        Schema::dropIfExists('listening_transcripts');
        Schema::dropIfExists('listening_dialogs');
        Schema::dropIfExists('listening_question_groups');
        Schema::dropIfExists('speaking_sections');
        Schema::dropIfExists('writing_tasks');
    }
};
