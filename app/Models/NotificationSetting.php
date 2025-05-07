<?php

// app/Models/NotificationSetting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    protected $fillable = [
        'user_id','type','enabled','frequency','before_hours','channels'
    ];

    protected $casts = [
        'enabled'      => 'boolean',
        'channels'     => 'array',
        'before_hours' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
