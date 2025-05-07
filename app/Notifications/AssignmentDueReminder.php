<?php

// app/Notifications/AssignmentDueReminder.php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AssignmentDueReminder extends Notification
{
    use Queueable;

    protected $assignment;

    public function __construct($assignment)
    {
        $this->assignment = $assignment;
    }

    public function via($notifiable)
{
    // return ['database'];  // simple, no settings table needed
    return ['database','broadcast'];
}

    public function toDatabase($notifiable)
    {
        return [
            'assignment_id' => $this->assignment->id,
            'title'         => $this->assignment->title,
            'due_date'      => $this->assignment->due_date,
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
