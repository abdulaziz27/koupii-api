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
        Schema::create('tests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('creator_id');
            $table->enum('type', ['reading','listening','speaking','writing']);
            $table->enum('difficulty', ['beginner','intermediate','advanced'])->default('beginner');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('time_limit_minutes')->nullable();
            $table->boolean('allow_repetition')->default(false);
            $table->integer('max_repetition_count')->nullable();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_published')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->foreign('creator_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('question_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('module', ['reading','listening','speaking','writing']);
            $table->json('structure')->nullable();
            $table->json('scoring_rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('test_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('test_id');
            $table->uuid('question_type_id');
            $table->integer('question_number')->nullable();
            $table->text('question_text')->nullable();
            $table->json('question_data')->nullable();
            $table->json('correct_answers')->nullable();
            $table->decimal('points_value', 8, 2)->default(1);
            $table->timestamps();

            $table->foreign('test_id')->references('id')->on('tests')->cascadeOnDelete();
            $table->foreign('question_type_id')->references('id')->on('question_types')->cascadeOnDelete();
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('question_id');
            $table->string('option_key')->nullable();
            $table->text('option_text')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('display_order')->nullable();
            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('test_questions')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
        Schema::dropIfExists('question_types');
        Schema::dropIfExists('test_questions');
        Schema::dropIfExists('question_options');
    }
};
