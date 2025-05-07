<form method="POST" action="{{ route('settings.notifications.update') }}">
  @csrf
  @foreach($settings as $setting)
    <div>
      <input type="checkbox" name="enabled[{{ $setting->id }}]" value="1"
        {{ $setting->enabled ? 'checked' : '' }}>
      <label>{{ ucfirst(str_replace('_',' ',$setting->type)) }}</label>
      <select name="frequency[{{ $setting->id }}]">
        <option value="once" {{ $setting->frequency=='once'? 'selected':'' }}>Once</option>
        <option value="hourly" {{ $setting->frequency=='hourly'? 'selected':'' }}>Hourly</option>
        <option value="daily" {{ $setting->frequency=='daily'? 'selected':'' }}>Daily</option>
      </select>
      <input type="number" name="before_hours[{{ $setting->id }}]" value="{{ $setting->before_hours }}">
    </div>
  @endforeach
  <button>Save Settings</button>
</form>
