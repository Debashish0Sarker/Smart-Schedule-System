<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-4">
        <h2>Welcome to the Dashboard</h2>

        <div class="card">
            <div class="card-body">
                <h5 class="card-title">User Info</h5>
                <p class="card-text">Name: {{ Auth::user()->name }}</p>
                <p class="card-text">Email: {{ Auth::user()->email }}</p>
                <p class="card-text">Student ID: {{ Auth::user()->student_id }}</p>
                <p class="card-text">Date of Birth: {{ Auth::user()->dob }}</p>
            </div>
        </div>

        <div class="mt-3">
            <a href="{{ route('logout') }}" class="btn btn-danger">Logout</a>
        </div>
    </div>
</body>
</html>
