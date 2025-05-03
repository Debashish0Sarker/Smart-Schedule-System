<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description'
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollments')
                    ->withPivot('grade', 'status')
                    ->withTimestamps();
    }
    
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}