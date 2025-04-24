<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations to create the users table.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student', function (Blueprint $table) {
            // Auto-incrementing primary key
            $table->id();

            // User's name
            $table->string('name');

            // Unique Student ID
            $table->string('student_id')->unique();

            // Date of Birth
            $table->date('dob');

            // Unique Email
            $table->string('email')->unique();

            // Hashed password
            $table->string('password');

            // Timestamps: created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations by dropping the users table.
     *
     * @return void
     */
    public function down()
    {
        // Drop the users table if migration is rolled back
        Schema::dropIfExists('student');
    }
}
