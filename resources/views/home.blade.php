<!DOCTYPE html>
<html>
<head>
    <title>Smart Schedule System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 3rem;
            padding-bottom: 3rem;
            background-color: #f8f9fa;
        }
        .hero-section {
            background: linear-gradient(to right, #4e73df, #224abe);
            color: white;
            padding: 4rem 0;
            margin-bottom: 2rem;
        }
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #4e73df;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">Smart Schedule System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/student/login">Student Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/admin/login">Admin Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container text-center">
            <h1>Welcome to Smart Schedule System</h1>
            <p class="lead">Streamlining education management for students and administrators</p>
            <div class="mt-4">
                <a href="/student/register" class="btn btn-light btn-lg me-2">Register as Student</a>
                <a href="/student/login" class="btn btn-outline-light btn-lg">Login</a>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row text-center">
            <div class="col-lg-4">
                <div class="card h-100 p-4 shadow-sm">
                    <div class="feature-icon">📚</div>
                    <h4>Course Management</h4>
                    <p>Enroll in courses, track progress, and manage your academic journey efficiently.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 p-4 shadow-sm">
                    <div class="feature-icon">📅</div>
                    <h4>Assignment Tracking</h4>
                    <p>Never miss a deadline with our comprehensive assignment tracking system.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100 p-4 shadow-sm">
                    <div class="feature-icon">📊</div>
                    <h4>Progress Reports</h4>
                    <p>Get detailed insights into your academic performance with customized reports.</p>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p>© 2025 Smart Schedule System. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
