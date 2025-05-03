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
}