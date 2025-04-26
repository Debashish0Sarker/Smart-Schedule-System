<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckOverdueAssignments extends Command
{
    protected $signature = 'assignments:check-overdue';
    protected $description = 'Check and mark overdue assignments, notify students';

    public function handle()
    {
        $now = Carbon::now();
        
        // Get all pending assignments that are past their due date
        $overdueAssignments = Assignment::where('status', 'pending')
            ->where('due_date', '<', $now)
            ->get();

        foreach ($overdueAssignments as $assignment) {
            // Mark assignment as overdue
            $assignment->update(['status' => 'overdue']);

            // Notify enrolled students
            $assignment->course->students->each(function($student) use ($assignment) {
                Notification::create([
                    'user_id' => $student->id,
                    'title' => 'Assignment Overdue',
                    'message' => "Assignment '{$assignment->title}' in {$assignment->course->name} is now overdue",
                    'type' => 'assignment_overdue',
                    'data' => [
                        'assignment_id' => $assignment->id,
                        'due_date' => $assignment->due_date
                    ]
                ]);
            });
        }

        $this->info("Checked and marked {$overdueAssignments->count()} overdue assignments.");
    }
}