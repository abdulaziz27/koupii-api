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
        Schema::create('question_breakdowns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('question_id');
            $table->text('explanation')->nullable();
            $table->boolean('has_highlight')->default(false);
            $table->timestamps();

            $table->foreign('question_id')->references('id')->on('test_questions')->cascadeOnDelete();
        });

        Schema::create('highlight_segments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('breakdown_id');
            $table->enum('passage_type', ['reading','listening']);
            $table->integer('start_char_index')->nullable();
            $table->integer('end_char_index')->nullable();
            $table->decimal('start_time_seconds', 8, 3)->nullable();
            $table->decimal('end_time_seconds', 8, 3)->nullable();
            $table->timestamps();

            $table->foreign('breakdown_id')->references('id')->on('question_breakdowns')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_breakdowns');
        Schema::dropIfExists('highlight_segments');
    }
};
