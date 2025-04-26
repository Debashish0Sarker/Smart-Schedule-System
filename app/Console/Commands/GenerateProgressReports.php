<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateProgressReports extends Command
{
    protected $signature = 'reports:generate {--type=daily}';
    protected $description = 'Generate progress reports for all active courses';

    public function handle()
    {
        $type = $this->option('type');
        $courses = Course::has('students')->get();

        foreach ($courses as $course) {
            foreach ($course->students as $student) {
                if ($type === 'daily') {
                    $this->generateDailyReport($course, $student);
                } else {
                    $this->generateWeeklyReport($course, $student);
                }
            }
        }

        $this->info("Successfully generated {$type} reports for all courses.");
    }

    private function generateDailyReport($course, $user)
    {
        $today = Carbon::today();
        
        // Get assignments due today
        $todaysAssignments = $course->assignments()
            ->whereDate('due_date', $today)
            ->get();

        $completedToday = $todaysAssignments->where('status', 'completed')->count();
        $totalToday = $todaysAssignments->count();
        $completionRate = $totalToday > 0 ? ($completedToday / $totalToday) * 100 : 100;

        $report = $user->progressReports()->create([
            'course_id' => $course->id,
            'report_type' => 'daily',
            'completion_rate' => $completionRate,
            'completed_assignments' => $completedToday,
            'pending_assignments' => $totalToday - $completedToday,
            'current_grade' => $user->courses()->where('course_id', $course->id)->first()->pivot->progress,
            'report_date' => $today,
            'performance_metrics' => [
                'on_time_submissions' => $completedToday,
                'daily_progress' => $user->courses()->where('course_id', $course->id)->first()->pivot->progress
            ]
        ]);

        // Send notification
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Daily Progress Report Available',
            'message' => "Your daily progress report for {$course->name} has been generated.",
            'type' => 'progress_report',
            'data' => ['report_id' => $report->id]
        ]);
    }

    private function generateWeeklyReport($course, $user)
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        $weeklyAssignments = $course->assignments()
            ->whereBetween('due_date', [$weekStart, $weekEnd])
            ->get();

        $completed = $weeklyAssignments->where('status', 'completed')->count();
        $total = $weeklyAssignments->count();
        $completionRate = $total > 0 ? ($completed / $total) * 100 : 100;

        $report = $user->progressReports()->create([
            'course_id' => $course->id,
            'report_type' => 'weekly',
            'completion_rate' => $completionRate,
            'completed_assignments' => $completed,
            'pending_assignments' => $total - $completed,
            'current_grade' => $user->courses()->where('course_id', $course->id)->first()->pivot->progress,
            'report_date' => Carbon::now(),
            'performance_metrics' => [
                'weekly_completion_trend' => $this->calculateWeeklyTrend($user, $course),
                'assignment_distribution' => [
                    'completed' => $weeklyAssignments->where('status', 'completed')->count(),
                    'pending' => $weeklyAssignments->where('status', 'pending')->count(),
                    'overdue' => $weeklyAssignments->where('status', 'overdue')->count()
                ]
            ]
        ]);

        // Send notification
        Notification::create([
            'user_id' => $user->id,
            'title' => 'Weekly Progress Report Available',
            'message' => "Your weekly progress report for {$course->name} has been generated.",
            'type' => 'progress_report',
            'data' => ['report_id' => $report->id]
        ]);
    }

    private function calculateWeeklyTrend($user, $course)
    {
        return collect(range(0, 3))->map(function($week) use ($user, $course) {
            $weekStart = Carbon::now()->subWeeks($week)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($week)->endOfWeek();
            
            return $user->progressReports()
                ->where('course_id', $course->id)
                ->where('report_type', 'weekly')
                ->whereBetween('report_date', [$weekStart, $weekEnd])
                ->first()
                ?->completion_rate ?? 0;
        })->toArray();
    }
}