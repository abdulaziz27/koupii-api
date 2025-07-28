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
            $table->string('name');
            $table->enum('type', ['reading', 'listening', 'speaking', 'writing']);
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced']);
            $table->text('description')->nullable();
            $table->integer('time_limit_minutes')->default(0);
            $table->integer('total_questions')->default(0);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_public')->default(false);
            $table->boolean('allow_repetition')->default(false);
            $table->integer('max_repetition_count')->default(1);
            $table->json('settings')->nullable();
            $table->timestamps();

            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};
