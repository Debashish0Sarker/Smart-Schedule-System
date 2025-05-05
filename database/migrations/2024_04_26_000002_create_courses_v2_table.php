<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Checking if the first courses table was created successfully
        if (Schema::hasTable('courses')) {
            // Since the first one exists, we don't need to create this one
            // We'll just add any missing columns to the existing table if needed
            Schema::table('courses', function (Blueprint $table) {
                if (!Schema::hasColumn('courses', 'code')) {
                    $table->string('code')->unique()->nullable();
                }
            });
        } else {
            // If the first migration fails, this one will create the courses table
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        // We don't drop the table in down() since the first migration handles that
    }
};