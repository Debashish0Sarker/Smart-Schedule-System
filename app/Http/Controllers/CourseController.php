<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with('students')->get();
        return response()->json($courses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:courses',
            'description' => 'nullable|string'
        ]);

        $course = Course::create($validated);
        return response()->json($course, 201);
    }

    public function show(Course $course)
    {
        return response()->json($course->load('students'));
    }

    public function getEnrolledStudents(Course $course)
    {
        $students = $course->students()->with('courses')->get();
        return response()->json($students);
    }

    public function updateGrades(Request $request, Course $course)
    {
        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.grade' => 'required|numeric|min:0|max:100'
        ]);

        foreach ($validated['grades'] as $grade) {
            $course->students()->updateExistingPivot($grade['student_id'], [
                'grade' => $grade['grade']
            ]);
        }

        return response()->json(['message' => 'Grades updated successfully']);
    }
}