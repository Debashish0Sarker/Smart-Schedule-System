<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Admin;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function searchStudents(Request $request)
    {
        $query = Student::query();

        if ($request->has('student_id')) {
            $query->where('student_id', 'like', '%' . $request->student_id . '%');
        }

        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }
        
        if ($request->has('q')) {
            // Generic search term that could match name, email, or student ID
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('email', 'like', '%' . $searchTerm . '%')
                  ->orWhere('student_id', 'like', '%' . $searchTerm . '%');
            });
        }

        return response()->json($query->with('courses')->get());
    }

    public function approveUser(Request $request, Student $student)
    {
        $student->update(['status' => 'approved']);

        Notification::create([
            'user_id' => $student->id,
            'title' => 'Account Approved',
            'message' => 'Your account has been approved by the administrator.',
            'type' => 'account_approval'
        ]);

        return response()->json(['message' => 'User approved successfully']);
    }

    public function deleteUser(Student $student)
    {
        $student->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function getComplaints()
    {
        $complaints = Notification::where('type', 'complaint')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($complaints);
    }

    public function replyToComplaint(Request $request, Notification $complaint)
    {
        $validated = $request->validate([
            'reply' => 'required|string'
        ]);

        Notification::create([
            'user_id' => $complaint->user_id,
            'title' => 'Admin Reply to Your Complaint',
            'message' => $validated['reply'],
            'type' => 'complaint_reply',
            'data' => ['original_complaint_id' => $complaint->id]
        ]);

        $complaint->update(['status' => 'replied']);

        return response()->json(['message' => 'Reply sent successfully']);
    }

    public function getUsersByCategory(Request $request)
    {
        $validated = $request->validate([
            'student_id_pattern' => 'required|string'
        ]);

        $students = Student::where('student_id', 'like', $validated['student_id_pattern'] . '%')
            ->with('courses')
            ->get()
            ->groupBy(function($student) {
                // Group by first 2 characters of student ID (or any other pattern)
                return substr($student->student_id, 0, 2);
            });

        return response()->json($students);
    }

    public function getDashboardStats()
    {
        $totalStudents = Student::count();
        $totalCourses = \App\Models\Course::count();
        $pendingApprovals = Student::where('status', 'pending')->count();
        $openComplaints = Notification::where('type', 'complaint')
            ->where('status', '!=', 'replied')
            ->count();

        return response()->json([
            'totalStudents' => $totalStudents,
            'totalCourses' => $totalCourses,
            'pendingApprovals' => $pendingApprovals,
            'openComplaints' => $openComplaints
        ]);
    }

    // Course management methods
    public function getCourses()
    {
        $courses = \App\Models\Course::withCount('students')
            ->orderBy('name')
            ->get();
            
        return response()->json($courses);
    }
    
    public function storeCourse(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:courses,code',
            'name' => 'required|string',
            'description' => 'nullable|string'
        ]);
        
        $course = \App\Models\Course::create($validated);
        
        return response()->json($course, 201);
    }
    
    public function updateCourse(Request $request, \App\Models\Course $course)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:courses,code,' . $course->id,
            'name' => 'required|string',
            'description' => 'nullable|string',
            'active' => 'boolean'
        ]);
        
        $course->update($validated);
        
        return response()->json($course);
    }
    
    public function deleteCourse(\App\Models\Course $course)
    {
        // Check if course has students
        if ($course->students()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete course with enrolled students'
            ], 400);
        }
        
        $course->delete();
        return response()->json(['message' => 'Course deleted successfully']);
    }
    
    public function getCourseDetails(\App\Models\Course $course)
    {
        return response()->json([
            'course' => $course->load('students'),
            'total_students' => $course->students()->count(),
            'average_grade' => $course->students()->avg('grade'),
            'assignments_count' => $course->assignments()->count()
        ]);
    }

    // Enrollment management methods
    public function manageEnrollments(Request $request, \App\Models\Course $course)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id',
            'action' => 'required|in:enroll,remove'
        ]);
        
        if ($validated['action'] === 'enroll') {
            $course->students()->syncWithoutDetaching($validated['student_ids']);
            
            // Notify enrolled students
            foreach ($validated['student_ids'] as $studentId) {
                Notification::create([
                    'user_id' => $studentId,
                    'title' => 'Course Enrollment',
                    'message' => "You have been enrolled in {$course->name}",
                    'type' => 'enrollment'
                ]);
            }
            
            return response()->json(['message' => 'Students enrolled successfully']);
        } else {
            $course->students()->detach($validated['student_ids']);
            
            // Notify removed students
            foreach ($validated['student_ids'] as $studentId) {
                Notification::create([
                    'user_id' => $studentId,
                    'title' => 'Course Enrollment Update',
                    'message' => "You have been removed from {$course->name}",
                    'type' => 'enrollment'
                ]);
            }
            
            return response()->json(['message' => 'Students removed successfully']);
        }
    }
    
    public function getEnrollmentStatus(\App\Models\Course $course)
    {
        $enrolledStudents = $course->students()->get(['students.id', 'name', 'email', 'student_id']);
        $availableStudents = Student::whereNotIn('id', $course->students()->pluck('students.id'))
            ->where('status', 'approved')
            ->get(['id', 'name', 'email', 'student_id']);
            
        return response()->json([
            'enrolled' => $enrolledStudents,
            'available' => $availableStudents
        ]);
    }

    // Progress monitoring methods
    public function getAcademicProgress()
    {
        // Get overall statistics
        $overallStats = [
            'average_grade' => Student::join('enrollments', 'students.id', '=', 'enrollments.student_id')
                ->whereNotNull('enrollments.grade')
                ->avg('enrollments.grade'),
            'passing_rate' => $this->calculatePassingRate(),
            'total_enrollments' => \App\Models\Course::withCount('students')->get()->sum('students_count'),
            'completion_rate' => $this->calculateCompletionRate()
        ];

        // Get course-wise performance
        $coursePerformance = \App\Models\Course::with(['students' => function($query) {
            $query->whereNotNull('enrollments.grade');
        }])
        ->withCount('students')
        ->get()
        ->map(function($course) {
            return [
                'id' => $course->id,
                'name' => $course->name,
                'code' => $course->code,
                'total_students' => $course->students_count,
                'average_grade' => $course->students->avg('pivot.grade'),
                'passing_count' => $course->students->where('pivot.grade', '>=', 60)->count(),
                'failing_count' => $course->students->where('pivot.grade', '<', 60)->count()
            ];
        });

        // Get top performing students
        $topStudents = Student::join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->select('students.*')
            ->selectRaw('AVG(enrollments.grade) as average_grade')
            ->whereNotNull('enrollments.grade')
            ->groupBy('students.id')
            ->orderByDesc('average_grade')
            ->limit(10)
            ->get();

        return response()->json([
            'overall_stats' => $overallStats,
            'course_performance' => $coursePerformance,
            'top_students' => $topStudents
        ]);
    }

    private function calculatePassingRate()
    {
        $totalGrades = \App\Models\Enrollment::whereNotNull('grade')->count();
        if ($totalGrades === 0) return 0;

        $passingGrades = \App\Models\Enrollment::where('grade', '>=', 60)->count();
        return ($passingGrades / $totalGrades) * 100;
    }

    private function calculateCompletionRate()
    {
        $totalAssignments = \App\Models\Assignment::count() * Student::count();
        if ($totalAssignments === 0) return 0;

        $completedAssignments = \App\Models\Assignment::whereHas('completions')->count();
        return ($completedAssignments / $totalAssignments) * 100;
    }

    public function generateProgressReport(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:course,student,overall',
            'id' => 'required_if:type,course,student|integer',
            'date_range' => 'nullable|in:week,month,semester'
        ]);

        $dateQuery = $this->getDateRangeQuery($validated['date_range'] ?? 'semester');

        switch ($validated['type']) {
            case 'course':
                return $this->generateCourseReport($validated['id'], $dateQuery);
            case 'student':
                return $this->generateStudentReport($validated['id'], $dateQuery);
            default:
                return $this->generateOverallReport($dateQuery);
        }
    }

    private function getDateRangeQuery($range)
    {
        switch ($range) {
            case 'week':
                return ['start' => now()->subWeek(), 'end' => now()];
            case 'month':
                return ['start' => now()->subMonth(), 'end' => now()];
            default: // semester
                return ['start' => now()->subMonths(6), 'end' => now()];
        }
    }

    private function generateCourseReport($courseId, $dateRange)
    {
        $course = \App\Models\Course::with(['students' => function($query) use ($dateRange) {
            $query->wherePivot('created_at', '>=', $dateRange['start'])
                  ->wherePivot('created_at', '<=', $dateRange['end']);
        }])->findOrFail($courseId);

        $report = [
            'course' => $course->only(['id', 'name', 'code']),
            'period' => [
                'start' => $dateRange['start']->format('Y-m-d'),
                'end' => $dateRange['end']->format('Y-m-d')
            ],
            'metrics' => [
                'total_students' => $course->students->count(),
                'average_grade' => $course->students->avg('pivot.grade'),
                'assignments_completion_rate' => $this->calculateCourseCompletionRate($course->id),
                'grade_distribution' => $this->calculateGradeDistribution($course->students)
            ]
        ];

        return response()->json($report);
    }

    private function calculateCourseCompletionRate($courseId)
    {
        $totalAssignments = \App\Models\Assignment::where('course_id', $courseId)->count();
        if ($totalAssignments === 0) return 0;

        $completedAssignments = \App\Models\Assignment::where('course_id', $courseId)
            ->whereHas('completions')
            ->count();

        return ($completedAssignments / $totalAssignments) * 100;
    }

    private function calculateGradeDistribution($students)
    {
        $distribution = [
            'A' => 0, // 90-100
            'B' => 0, // 80-89
            'C' => 0, // 70-79
            'D' => 0, // 60-69
            'F' => 0  // Below 60
        ];

        foreach ($students as $student) {
            $grade = $student->pivot->grade;
            if ($grade >= 90) $distribution['A']++;
            elseif ($grade >= 80) $distribution['B']++;
            elseif ($grade >= 70) $distribution['C']++;
            elseif ($grade >= 60) $distribution['D']++;
            else $distribution['F']++;
        }

        return $distribution;
    }
}