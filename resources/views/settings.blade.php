@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Settings</h1>
  </nav>

  <form method="POST" action="/settings">
    @if(\Session::get('error'))
      <div class="alert alert-danger text-center" role="alert">
        {{ \Session::get('error') }}
      </div>
    @endif

    @if(\Session::get('success'))
      <div class="alert alert-success text-center" role="alert">
        {{ \Session::get('success') }}
      </div>
    @endif

    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
    <div class="form-group mt-3">
      <label for="warningLimit">Items Warning Limit</label>
      <input type="number" name="warning-limit" class="form-control" id="warningLimit" placeholder="Warning Limit" value="{{ isset($warning_limit) ? $warning_limit : '' }}" autocomplete="off">
    </div>
    <button type="submit" class="btn btn-outline-primary mt-3">Update</button>
  </form>

  <hr class="my-5">

  <div class="text-center">
    <a href="/logout" class="btn col-12 btn-outline-danger">Logout Account</a>
  </div>
@endsection