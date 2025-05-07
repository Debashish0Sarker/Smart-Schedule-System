<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        // paginate in the controller
        $notes = auth()->user()
                     ->notifications()
                     ->paginate(20);

        // simple view call
        return view('notifications.index', compact('notes'));
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }
}
