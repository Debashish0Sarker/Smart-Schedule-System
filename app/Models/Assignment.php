<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'due_date',
        'weight',
        'total_points',
        'status'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'weight' => 'decimal:2',
        'total_points' => 'decimal:2'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}