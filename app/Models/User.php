<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Use custom table name
    protected $table = 'student';

    // The attributes that are mass assignable
    protected $fillable = [
        'name',
        'student_id',
        'dob',
        'email',
        'password',
    ];

    // Relationship: User has many notification settings
    public function notificationSettings()
    {
        return $this->hasMany(NotificationSetting::class);
    }

    // Optional: Used for broadcasting notifications (e.g., Laravel Echo)
    public function routeNotificationForBroadcast()
    {
        return 'user.' . $this->id;
    }
}
