<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'student_courses')
                    ->withPivot(['status', 'grade', 'progress', 'custom_weights'])
                    ->withTimestamps();
    }

    public function progressReports()
    {
        return $this->hasMany(ProgressReport::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function getUnreadNotificationsCount()
    {
        return $this->notifications()->where('status', 'unread')->count();
    }

    public function enrollInCourse(Course $course)
    {
        return $this->courses()->attach($course->id, [
            'status' => 'enrolled',
            'progress' => 0,
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
