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
        Schema::create('vocabulary_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('color_code')->nullable();
            $table->timestamps();
        });

        Schema::create('vocabularies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('teacher_id');
            $table->uuid('category_id')->nullable();
            $table->string('word');
            $table->string('translation')->nullable();
            $table->string('spelling')->nullable();
            $table->text('explanation')->nullable();
            $table->string('audio_file_path')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('vocabulary_categories')->nullOnDelete();
        });

        Schema::create('vocabulary_bookmarks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('vocabulary_id');
            $table->boolean('is_bookmarked')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vocabulary_id')->references('id')->on('vocabularies')->onDelete('cascade');
        });

        Schema::create('class_vocabularies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('class_id');
            $table->uuid('vocabulary_id');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('classes')->cascadeOnDelete();
            $table->foreign('vocabulary_id')->references('id')->on('vocabularies')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_vocabularies');
        Schema::dropIfExists('vocabulary_bookmarks');
        Schema::dropIfExists('vocabularies');
        Schema::dropIfExists('vocabulary_categories');
    }
};
