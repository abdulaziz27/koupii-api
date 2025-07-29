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
        Schema::create('teacher_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('name', ['Basic', 'Pro', 'Premium']);
            $table->decimal('price', 8, 2)->default(0);
            $table->integer('max_students')->default(0);
            $table->integer('max_classrooms')->default(0);
            $table->boolean('can_create_tests')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_plans');
    }
};
