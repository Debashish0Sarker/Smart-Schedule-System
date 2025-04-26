<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'report_type',
        'completion_rate',
        'current_grade',
        'completed_assignments',
        'pending_assignments',
        'performance_metrics',
        'report_date'
    ];

    protected $casts = [
        'completion_rate' => 'decimal:2',
        'current_grade' => 'decimal:2',
        'performance_metrics' => 'json',
        'report_date' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}