<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Notification;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Course $course)
    {
        $assignments = $course->assignments()
                            ->orderBy('due_date')
                            ->get();
        return response()->json($assignments);
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'weight' => 'required|numeric|min:0|max:100',
            'total_points' => 'required|numeric|min:0'
        ]);

        $assignment = $course->assignments()->create($validated);

        // Notify enrolled students
        $course->students->each(function($student) use ($assignment) {
            Notification::create([
                'user_id' => $student->id,
                'title' => 'New Assignment Added',
                'message' => "A new assignment '{$assignment->title}' has been added to {$assignment->course->name}",
                'type' => 'assignment',
                'data' => ['assignment_id' => $assignment->id]
            ]);
        });

        return response()->json($assignment, 201);
    }

    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'sometimes|date',
            'weight' => 'sometimes|numeric|min:0|max:100',
            'total_points' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:pending,completed,overdue'
        ]);

        $assignment->update($validated);

        if (isset($validated['due_date']) || isset($validated['weight'])) {
            // Notify enrolled students about the change
            $assignment->course->students->each(function($student) use ($assignment) {
                Notification::create([
                    'user_id' => $student->id,
                    'title' => 'Assignment Updated',
                    'message' => "Assignment '{$assignment->title}' has been updated",
                    'type' => 'assignment_update',
                    'data' => ['assignment_id' => $assignment->id]
                ]);
            });
        }

        return response()->json($assignment);
    }

    public function delete(Assignment $assignment)
    {
        $assignment->delete();
        return response()->json(['message' => 'Assignment deleted successfully']);
    }

    public function getStudentProgress(Request $request, Assignment $assignment)
    {
        $user = $request->user();
        $progress = $user->courses()
                        ->where('course_id', $assignment->course_id)
                        ->first()
                        ->pivot
                        ->progress;

        return response()->json([
            'assignment' => $assignment,
            'progress' => $progress
        ]);
    }

    public function submitAssignment(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'submission' => 'required|string',
            'completed_at' => 'required|date'
        ]);

        $user = $request->user();
        
        // Update assignment status
        $status = now() > $assignment->due_date ? 'overdue' : 'completed';
        $assignment->status = $status;
        $assignment->save();

        // Calculate and update course progress
        $totalAssignments = $assignment->course->assignments()->count();
        $completedAssignments = $assignment->course->assignments()
                                        ->where('status', 'completed')
                                        ->orWhere('status', 'overdue')
                                        ->count();
        
        $progress = ($completedAssignments / $totalAssignments) * 100;
        $user->updateCourseProgress($assignment->course, $progress);

        return response()->json([
            'message' => 'Assignment submitted successfully',
            'status' => $status,
            'progress' => $progress
        ]);
    }

    public function getUpcomingAssignments(Request $request)
    {
        $user = $request->user();
        $now = now();
        $oneWeekFromNow = $now->copy()->addDays(7);
        
        // Get IDs of courses the student is enrolled in
        $courseIds = $user->courses()->pluck('course_id');
        
        // Get upcoming assignments for these courses
        $upcomingAssignments = Assignment::whereIn('course_id', $courseIds)
            ->where('due_date', '>=', $now)
            ->where('due_date', '<=', $oneWeekFromNow)
            ->orderBy('due_date')
            ->with('course')
            ->get();
            
        return response()->json($upcomingAssignments);
    }

    public function getStudentAssignments(Request $request)
    {
        $user = $request->user();
        $status = $request->query('status');
        
        // Get IDs of courses the student is enrolled in
        $courseIds = $user->courses()->pluck('course_id');
        
        // Base query for assignments in enrolled courses
        $query = Assignment::whereIn('course_id', $courseIds)
                 ->with('course');
                 
        // Filter by status if provided
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        
        $assignments = $query->orderBy('due_date')->get();
        
        return response()->json($assignments);
    }
    
    public function getStudentCourseAssignments(Request $request, Course $course)
    {
        $user = $request->user();
        $status = $request->query('status');
        
        // Check if student is enrolled in this course
        if (!$user->courses()->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }
        
        // Query for assignments in this course
        $query = Assignment::where('course_id', $course->id);
        
        // Filter by status if provided
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        
        $assignments = $query->with('course')->orderBy('due_date')->get();
        
        return response()->json($assignments);
    }
    
    public function markAsComplete(Request $request, Assignment $assignment)
    {
        $user = $request->user();
        
        // Check if student is enrolled in the course this assignment belongs to
        if (!$user->courses()->where('course_id', $assignment->course_id)->exists()) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }
        
        // Update assignment status
        $assignment->status = 'completed';
        $assignment->save();
        
        // Calculate and update course progress
        $totalAssignments = $assignment->course->assignments()->count();
        $completedAssignments = $assignment->course->assignments()
                                    ->where('status', 'completed')
                                    ->orWhere('status', 'overdue')
                                    ->count();
                                    
        $progress = ($completedAssignments / $totalAssignments) * 100;
        $user->updateCourseProgress($assignment->course, $progress);
        
        return response()->json([
            'message' => 'Assignment marked as complete',
            'status' => 'completed',
            'progress' => $progress
        ]);
    }
}