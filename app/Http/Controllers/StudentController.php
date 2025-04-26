<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index()
    {
        $students = Student::with('courses')->get();
        return response()->json($students);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:students',
            'password' => 'required|string|min:8',
            'student_id' => 'required|string|unique:students'
        ]);

        $student = Student::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'student_id' => $validated['student_id']
        ]);

        return response()->json([
            'message' => 'Student registered successfully',
            'student' => $student
        ], 201);
    }

    public function enrollCourse(Request $request, Course $course)
    {
        $student = $request->user();
        
        if ($student->courses()->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'Already enrolled in this course'], 422);
        }

        $student->courses()->attach($course->id, ['status' => 'enrolled']);
        return response()->json(['message' => 'Successfully enrolled in course']);
    }

    public function viewCourses(Request $request)
    {
        $student = $request->user();
        $courses = $student->courses()->with('students')->get();
        return response()->json($courses);
    }

    public function updateGrade(Request $request, Course $course)
    {
        $validated = $request->validate([
            'grade' => 'required|numeric|min:0|max:100'
        ]);

        $student = $request->user();
        $student->courses()->updateExistingPivot($course->id, [
            'grade' => $validated['grade']
        ]);

        return response()->json(['message' => 'Grade updated successfully']);
    }
}