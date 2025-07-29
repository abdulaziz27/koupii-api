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
        Schema::create('public_tests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('admin_id');
            $table->string('name');
            $table->enum('type', ['reading','listening','speaking','writing']);
            $table->enum('difficulty', ['beginner','intermediate','advanced']);
            $table->text('description')->nullable();
            $table->json('question_types')->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->integer('total_attempts')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_tests');
    }
};
