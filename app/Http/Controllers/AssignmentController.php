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
}