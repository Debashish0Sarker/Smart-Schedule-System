<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title','My App')</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0-beta2/dist/css/bootstrap.min.css">
</head>
<body>
  <nav class="navbar navbar-expand navbar-light bg-light px-4">
    <a class="navbar-brand" href="{{ url('/') }}">MyApp</a>
    <ul class="navbar-nav ms-auto">
      @auth
        @php $count = Auth::user()->unreadNotifications->count(); @endphp
        <li class="nav-item">
          <a class="nav-link position-relative" href="{{ route('notifications') }}">
            🔔
            @if($count)
              <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">
                {{ $count }}
              </span>
            @endif
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="{{ route('logout') }}">Logout</a>
        </li>
      @else
        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
      @endauth
    </ul>
  </nav>

  <div class="container my-4">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @yield('content')
  </div>
</body>
</html>
