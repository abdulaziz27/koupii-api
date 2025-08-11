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
            $table->enum('difficulty', ['beginner','intermediate','advanced'])->default('beginner');
            $table->text('description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->integer('total_attempts')->default(0);
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('public_test_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('public_test_id');
            $table->decimal('score', 8, 2)->nullable();
            $table->integer('time_spent_seconds')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('public_test_id')->references('id')->on('public_tests')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('public_tests');
        Schema::dropIfExists('public_test_attempts');
    }
};
