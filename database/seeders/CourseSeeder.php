<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $courses = [
            [
                'name' => 'Introduction to Computer Science',
                'code' => 'CS101',
                'description' => 'An introductory course covering the basics of computer science and programming.'
            ],
            [
                'name' => 'Data Structures and Algorithms',
                'code' => 'CS201',
                'description' => 'Advanced course exploring data structures, algorithm design and analysis.'
            ],
            [
                'name' => 'Database Systems',
                'code' => 'CS301',
                'description' => 'Fundamentals of database design, SQL, and database management systems.'
            ],
            [
                'name' => 'Web Development',
                'code' => 'CS350',
                'description' => 'Modern web development practices including frontend and backend technologies.'
            ],
            [
                'name' => 'Artificial Intelligence',
                'code' => 'CS401',
                'description' => 'Introduction to AI concepts including machine learning, neural networks, and natural language processing.'
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}