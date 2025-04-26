<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('progress_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->enum('report_type', ['daily', 'weekly', 'semester']);
            $table->decimal('completion_rate', 5, 2);
            $table->decimal('current_grade', 5, 2)->nullable();
            $table->integer('completed_assignments');
            $table->integer('pending_assignments');
            $table->json('performance_metrics')->nullable();
            $table->date('report_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('progress_reports');
    }
};