<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Student extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'student_id',
        'status'
    ];

    protected $hidden = [
        'password',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'enrollments')
                    ->withPivot('grade', 'status')
                    ->withTimestamps();
    }
    
    public function progressReports()
    {
        return $this->hasMany(ProgressReport::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }
    
    public function getUnreadNotificationsCount()
    {
        return $this->notifications()->where('status', 'unread')->count();
    }

    public function enrollInCourse(Course $course)
    {
        return $this->courses()->attach($course->id, [
            'status' => 'enrolled'
        ]);
    }

    public function dropCourse(Course $course)
    {
        return $this->courses()->updateExistingPivot($course->id, [
            'status' => 'dropped'
        ]);
    }

    public function updateCourseProgress(Course $course, $progress)
    {
        return $this->courses()->updateExistingPivot($course->id, [
            'progress' => $progress
        ]);
    }
}