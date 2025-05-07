@extends('layouts.app')  {{-- or your header/footer file --}}
@section('title','Your Notifications')

@section('content')
  <h1>Your Notifications</h1>

  <form method="POST" action="{{ route('notifications.readAll') }}">
    @csrf
    <button class="btn btn-sm btn-outline-primary mb-3">
      Mark All Read
    </button>
  </form>

  <ul class="list-group">
    @foreach($notes as $note)
      <li class="
          list-group-item
          {{ is_null($note->read_at) ? 'list-group-item-warning' : '' }}
        ">
        {{-- Display whatever you put in toDatabase() --}}
        <strong>{{ $note->data['title'] }}</strong><br>
        <small>Due: {{ $note->data['due_date'] }}</small>
        @if(is_null($note->read_at))
          <span class="badge bg-danger ms-2">new</span>
        @endif
        <div class="text-muted small">{{ $note->created_at->diffForHumans() }}</div>
      </li>
    @endforeach
  </ul>

  {{ $notes->links() }}
@endsection
