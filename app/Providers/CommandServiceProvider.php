<?php

namespace App\Providers;

use App\Console\Commands\GenerateProgressReports;
use App\Console\Commands\CheckOverdueAssignments;
use Illuminate\Support\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            GenerateProgressReports::class,
            CheckOverdueAssignments::class,
        ]);
    }
}