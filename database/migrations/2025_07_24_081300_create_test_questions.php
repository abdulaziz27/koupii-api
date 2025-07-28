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
        Schema::create('test_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('test_id');
            $table->uuid('passage_id')->nullable();
            $table->uuid('question_type_id');
            $table->uuid('question_instruction_id')->nullable();
            $table->integer('question_number');
            $table->string('question_group')->nullable();
            $table->text('question_text');
            $table->json('question_data')->nullable();
            $table->json('correct_answers')->nullable();
            $table->decimal('points_value', 8, 2)->default(0);
            $table->text('explanation')->nullable();
            $table->integer('audio_start_time')->nullable();
            $table->integer('audio_end_time')->nullable();
            $table->timestamps();

            $table->foreign('test_id')->references('id')->on('tests')->onDelete('cascade');
            $table->foreign('passage_id')->references('id')->on('test_passages')->onDelete('cascade');
            $table->foreign('question_type_id')->references('id')->on('question_types')->onDelete('cascade');
            $table->foreign('question_instruction_id')->references('id')->on('question_instructions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_questions');
    }
};
