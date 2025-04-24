<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Specify the custom table name
    protected $table = 'student';  // Ensure this matches your database table name

    // The attributes that are mass assignable
    protected $fillable = [
        'name', 'student_id', 'dob', 'email', 'password'
    ];

    // You can add more methods here if necessary
}
