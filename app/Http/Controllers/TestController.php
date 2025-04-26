<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function test()
    {
        try {
            DB::connection()->getPdo();
            return response()->json([
                'message' => 'Database connection successful',
                'database' => DB::connection()->getDatabaseName()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}