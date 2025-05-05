<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Course;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AssignmentSeeder extends Seeder
{
    public function run()
    {
        // Get all course IDs
        $courses = Course::pluck('id')->toArray();
        
        if (empty($courses)) {
            echo "No courses found. Please run CourseSeeder first.\n";
            return;
        }

        $assignments = [
            [
                'title' => 'Introduction to Programming - Lab Assignment',
                'description' => 'Complete the basic programming exercises in the lab manual.',
                'due_date' => Carbon::now()->addDays(7),
                'weight' => 10.0,
                'total_points' => 100.0,
                'status' => 'pending'
            ],
            [
                'title' => 'Data Structures Implementation',
                'description' => 'Implement a linked list, stack, and queue in your preferred programming language.',
                'due_date' => Carbon::now()->addDays(14),
                'weight' => 15.0,
                'total_points' => 100.0,
                'status' => 'pending'
            ],
            [
                'title' => 'Database Design Project',
                'description' => 'Design a normalized database schema for the given case study.',
                'due_date' => Carbon::now()->addDays(21),
                'weight' => 20.0,
                'total_points' => 100.0,
                'status' => 'pending'
            ],
            [
                'title' => 'Web App Development',
                'description' => 'Build a simple web application that demonstrates CRUD operations.',
                'due_date' => Carbon::now()->addDays(28),
                'weight' => 25.0,
                'total_points' => 100.0,
                'status' => 'pending'
            ],
            [
                'title' => 'AI Algorithm Implementation',
                'description' => 'Implement a basic machine learning algorithm for the provided dataset.',
                'due_date' => Carbon::now()->addDays(35),
                'weight' => 30.0,
                'total_points' => 100.0,
                'status' => 'pending'
            ],
        ];

        // Associate each assignment with a course
        foreach ($assignments as $key => $assignmentData) {
            // Use modulo to cycle through courses if there are more assignments than courses
            $courseIndex = $key % count($courses);
            
            $assignmentData['course_id'] = $courses[$courseIndex];
            Assignment::create($assignmentData);
        }
    }
}