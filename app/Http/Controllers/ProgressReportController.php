<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ProgressReport;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProgressReportController extends Controller
{
    public function generateDailyReport(Request $request, Course $course)
    {
        $user = $request->user();
        $today = Carbon::today();
        
        // Get assignments due today
        $todaysAssignments = $course->assignments()
            ->whereDate('due_date', $today)
            ->get();

        // Calculate completion metrics
        $completedToday = $todaysAssignments->where('status', 'completed')->count();
        $totalToday = $todaysAssignments->count();
        $completionRate = $totalToday > 0 ? ($completedToday / $totalToday) * 100 : 100;

        // Get overall course progress
        $courseProgress = $user->courses()
            ->where('course_id', $course->id)
            ->first()
            ->pivot
            ->progress;

        $report = ProgressReport::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'report_type' => 'daily',
            'completion_rate' => $completionRate,
            'completed_assignments' => $completedToday,
            'pending_assignments' => $totalToday - $completedToday,
            'current_grade' => $courseProgress,
            'report_date' => $today,
            'performance_metrics' => [
                'on_time_submissions' => $completedToday,
                'daily_progress' => $courseProgress
            ]
        ]);

        return response()->json($report);
    }

    public function generateWeeklyReport(Request $request, Course $course)
    {
        $user = $request->user();
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Get this week's assignments
        $weeklyAssignments = $course->assignments()
            ->whereBetween('due_date', [$weekStart, $weekEnd])
            ->get();

        $completed = $weeklyAssignments->where('status', 'completed')->count();
        $total = $weeklyAssignments->count();
        $completionRate = $total > 0 ? ($completed / $total) * 100 : 100;

        // Calculate weekly metrics
        $report = ProgressReport::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'report_type' => 'weekly',
            'completion_rate' => $completionRate,
            'completed_assignments' => $completed,
            'pending_assignments' => $total - $completed,
            'current_grade' => $user->courses()->where('course_id', $course->id)->first()->pivot->progress,
            'report_date' => Carbon::now(),
            'performance_metrics' => [
                'weekly_completion_trend' => $this->calculateWeeklyTrend($user, $course),
                'assignment_distribution' => $this->getAssignmentDistribution($weeklyAssignments)
            ]
        ]);

        return response()->json($report);
    }

    private function calculateWeeklyTrend(User $user, Course $course)
    {
        $lastFourWeeks = collect(range(0, 3))->map(function($week) use ($user, $course) {
            $weekStart = Carbon::now()->subWeeks($week)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($week)->endOfWeek();
            
            return ProgressReport::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('report_type', 'weekly')
                ->whereBetween('report_date', [$weekStart, $weekEnd])
                ->first()
                ?->completion_rate ?? 0;
        });

        return $lastFourWeeks->toArray();
    }

    private function getAssignmentDistribution($assignments)
    {
        return [
            'completed' => $assignments->where('status', 'completed')->count(),
            'pending' => $assignments->where('status', 'pending')->count(),
            'overdue' => $assignments->where('status', 'overdue')->count()
        ];
    }

    public function getReportsByType(Request $request, Course $course, $type)
    {
        $user = $request->user();
        
        $reports = ProgressReport::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('report_type', $type)
            ->orderBy('report_date', 'desc')
            ->get();

        return response()->json($reports);
    }

    public function getDelayTracking(Request $request, Course $course)
    {
        $user = $request->user();
        
        $assignments = $course->assignments()
            ->where('status', 'overdue')
            ->get();

        $delayMetrics = [
            'total_delayed' => $assignments->count(),
            'average_delay' => $assignments->avg(function($assignment) {
                return Carbon::parse($assignment->due_date)->diffInDays(Carbon::parse($assignment->completed_at));
            }),
            'delayed_assignments' => $assignments->map(function($assignment) {
                return [
                    'title' => $assignment->title,
                    'due_date' => $assignment->due_date,
                    'completed_at' => $assignment->completed_at,
                    'delay_days' => Carbon::parse($assignment->due_date)
                        ->diffInDays(Carbon::parse($assignment->completed_at))
                ];
            })
        ];

        return response()->json($delayMetrics);
    }
}