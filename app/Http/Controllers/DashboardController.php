<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // Ensure the user is authenticated before accessing this page
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Display the dashboard
    public function index()
    {
        return view('dashboard');
    }
}
