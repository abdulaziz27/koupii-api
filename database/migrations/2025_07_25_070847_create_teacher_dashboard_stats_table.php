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
        Schema::create('teacher_dashboard_stats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('teacher_id');
            $table->integer('total_students')->default(0);
            $table->integer('total_classes')->default(0);
            $table->integer('total_tests_created')->default(0);
            $table->integer('total_assignments')->default(0);
            $table->json('monthly_activity')->nullable();
            $table->json('class_performance_summary')->nullable();
            $table->timestamp('last_calculated')->nullable();
            $table->timestamps();

            $table->foreign('teacher_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_dashboard_stats');
    }
};
