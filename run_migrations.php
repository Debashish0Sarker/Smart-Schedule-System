<?php
// This script runs the Laravel migrations using the Artisan facade
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Run migrations
echo "Running migrations...\n";
$exitCode = Illuminate\Support\Facades\Artisan::call('migrate:fresh');
echo Illuminate\Support\Facades\Artisan::output();

if ($exitCode === 0) {
    echo "Migrations completed successfully!\n";
    
    // Seed the database
    echo "Seeding database...\n";
    $exitCode = Illuminate\Support\Facades\Artisan::call('db:seed');
    echo Illuminate\Support\Facades\Artisan::output();
    
    if ($exitCode === 0) {
        echo "Database seeded successfully!\n";
    } else {
        echo "Error seeding database. Exit code: {$exitCode}\n";
    }
} else {
    echo "Error running migrations. Exit code: {$exitCode}\n";
}