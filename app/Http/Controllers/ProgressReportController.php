<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ProgressReport;
use App\Models\User;
use App\Models\Student;
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
            ->progress ?? 0;

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
        
        $courseData = $user->courses()->where('course_id', $course->id)->first();
        $currentProgress = $courseData ? ($courseData->pivot->progress ?? 0) : 0;

        // Calculate weekly metrics
        $report = ProgressReport::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'report_type' => 'weekly',
            'completion_rate' => $completionRate,
            'completed_assignments' => $completed,
            'pending_assignments' => $total - $completed,
            'current_grade' => $currentProgress,
            'report_date' => Carbon::now(),
            'performance_metrics' => [
                'weekly_completion_trend' => $this->calculateWeeklyTrend($user, $course),
                'assignment_distribution' => $this->getAssignmentDistribution($weeklyAssignments)
            ]
        ]);

        return response()->json($report);
    }

    private function calculateWeeklyTrend($user, Course $course)
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

    // Get reports for a specific course and type
    public function getCourseReportsByType(Request $request, Course $course, $type)
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

    public function getProgressSummary(Request $request)
    {
        $user = $request->user();
        
        // Get all enrolled courses
        $courses = $user->courses;
        
        // Calculate overall completion percentage across all courses
        $totalProgress = 0;
        foreach ($courses as $course) {
            $totalProgress += $course->pivot->progress ?? 0;
        }
        
        $overallCompletion = $courses->count() > 0 
            ? $totalProgress / $courses->count() 
            : 0;
            
        // Get all assignments for enrolled courses
        $courseIds = $courses->pluck('id');
        $assignments = \App\Models\Assignment::whereIn('course_id', $courseIds)->get();
        
        $totalAssignments = $assignments->count();
        $completedAssignments = $assignments->where('status', 'completed')->count();
        $pendingAssignments = $assignments->where('status', 'pending')->count();
        $overdueAssignments = $assignments->where('status', 'overdue')->count();
        
        return response()->json([
            'overall_completion' => round($overallCompletion, 2),
            'total_assignments' => $totalAssignments,
            'completed_assignments' => $completedAssignments,
            'pending_assignments' => $pendingAssignments,
            'overdue_assignments' => $overdueAssignments
        ]);
    }

    // Get reports by type for all courses
    public function getReportsByType(Request $request, $type)
    {
        // Validate report type
        if (!in_array($type, ['daily', 'weekly', 'semester'])) {
            return response()->json(['message' => 'Invalid report type'], 400);
        }
        
        $user = $request->user();
        
        // Get all reports of the specified type for this user
        $reports = ProgressReport::where('user_id', $user->id)
            ->where('report_type', $type)
            ->orderBy('report_date', 'desc')
            ->with('course')
            ->get();
            
        // If no reports exist, generate some for demo purposes
        if ($reports->isEmpty()) {
            $reports = $this->generateDemoReports($user, $type);
        }
        
        return response()->json($reports);
    }
    
    private function generateDemoReports($user, $type)
    {
        // For demo purposes, create some sample reports if none exist
        $demoReports = [];
        $courses = $user->courses;
        
        if ($courses->isEmpty()) {
            return collect($demoReports);
        }
        
        foreach ($courses as $course) {
            // Generate different dates based on report type
            if ($type === 'daily') {
                $reportDates = [
                    Carbon::today(),
                    Carbon::yesterday(),
                    Carbon::now()->subDays(2)
                ];
            } elseif ($type === 'weekly') {
                $reportDates = [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->subWeek()->startOfWeek(),
                    Carbon::now()->subWeeks(2)->startOfWeek()
                ];
            } else { // semester
                $reportDates = [
                    Carbon::now(),
                    Carbon::now()->subMonths(1),
                    Carbon::now()->subMonths(3)
                ];
            }
            
            foreach ($reportDates as $date) {
                $progress = rand(50, 95);
                $totalAssignments = rand(5, 15);
                $completedAssignments = intval($totalAssignments * ($progress/100));
                
                $report = new ProgressReport([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'report_type' => $type,
                    'completion_rate' => $progress,
                    'current_grade' => rand(70, 95),
                    'completed_assignments' => $completedAssignments,
                    'pending_assignments' => $totalAssignments - $completedAssignments,
                    'report_date' => $date,
                    'performance_metrics' => null
                ]);
                
                // Set the course relationship manually for the demo data
                $report->setRelation('course', $course);
                $demoReports[] = $report;
            }
        }
        
        return collect($demoReports);
    }
}