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
        Schema::create('question_type_performances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('question_type_name');
            $table->enum('skill_category', ['reading','listening','speaking','writing']);
            $table->integer('total_attempts')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->decimal('accuracy_percentage', 5, 2)->default(0);
            $table->json('performance_history')->nullable();
            $table->timestamp('last_attempt')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_type_performances');
    }
};
