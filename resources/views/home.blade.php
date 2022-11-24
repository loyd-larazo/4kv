@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light px-3">
    <h1>Dashboard</h1>

    <span class="fw-bold">
      Today: {{ date('Y').'/'.date('m').'/'.date('d') }} <span id="time"></span>
    </span>
  </nav>

  <script>
    $(function() {
      setInterval(function() {
        const d = new Date();
        $('#time').html(`${d.getHours()}:${d.getMinutes()}:${d.getSeconds()}`);
      }, 1000);
    });
  </script>
@endsection