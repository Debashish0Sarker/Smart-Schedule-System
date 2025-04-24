<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .register-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }

        .register-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .register-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .register-container button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .register-container button:hover {
            background-color: #45a049;
        }

        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .register-link {
            text-align: center;
            margin-top: 10px;
        }

        .register-link a {
            text-decoration: none;
            color: #007BFF;
        }

        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="register-container">
        <h2>Student Registration</h2>

        <!-- Display validation errors -->
        @if ($errors->any())
            <div class="error-message">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/register') }}">
            @csrf
            <div>
                <label for="name">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div>
                <label for="student_id">Student ID</label>
                <input type="text" name="student_id" value="{{ old('student_id') }}" required>
            </div>
            <div>
                <label for="dob">Date of Birth</label>
                <input type="date" name="dob" value="{{ old('dob') }}" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" name="password" required>
            </div>
            <div>
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" name="password_confirmation" required>
            </div>
            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="{{ route('login') }}">Login</a></p>
    </div>

</body>
</html>
