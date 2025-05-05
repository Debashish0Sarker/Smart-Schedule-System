<!DOCTYPE html>
<html>
<head>
    <title>Student Registration - Smart Schedule System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Student Registration</div>
                    <div class="card-body">
                        <form id="registrationForm">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="student_id" class="form-label">Student ID</label>
                                <input type="text" class="form-control" id="student_id" name="student_id" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="alert alert-danger d-none" id="errorAlert"></div>
                            <div class="alert alert-success d-none" id="successAlert"></div>
                            <button type="submit" class="btn btn-primary">Register</button>
                            <p class="mt-3">
                                Already have an account? <a href="/student/login">Login</a>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        document.getElementById('registrationForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const student_id = document.getElementById('student_id').value;
            const password = document.getElementById('password').value;
            
            const errorAlert = document.getElementById('errorAlert');
            const successAlert = document.getElementById('successAlert');
            
            errorAlert.classList.add('d-none');
            successAlert.classList.add('d-none');
            
            try {
                // First, get a CSRF token
                await fetch('/sanctum/csrf-cookie', {
                    method: 'GET',
                    credentials: 'include',
                });
                
                const response = await fetch('/api/students/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    credentials: 'include',
                    body: JSON.stringify({ 
                        name, 
                        email, 
                        student_id, 
                        password
                    })
                });
                
                const data = await response.json();
                
                if (response.ok) {
                    successAlert.textContent = 'Registration successful! Your account is pending approval by an administrator. You will be redirected to the login page.';
                    successAlert.classList.remove('d-none');
                    
                    // Clear form
                    this.reset();
                    
                    // Redirect to login after 3 seconds
                    setTimeout(() => {
                        window.location.href = '/student/login';
                    }, 3000);
                } else {
                    // Show error message
                    const errorMessages = [];
                    if (data.errors) {
                        Object.values(data.errors).forEach(error => {
                            errorMessages.push(error);
                        });
                    } else {
                        errorMessages.push(data.message || 'Registration failed');
                    }
                    
                    errorAlert.innerHTML = errorMessages.join('<br>');
                    errorAlert.classList.remove('d-none');
                }
            } catch (error) {
                console.error('Error:', error);
                errorAlert.textContent = 'An error occurred. Please try again.';
                errorAlert.classList.remove('d-none');
            }
        });
    </script>
</body>
</html>