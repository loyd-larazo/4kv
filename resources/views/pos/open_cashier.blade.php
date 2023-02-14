@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>{{ $isClosed ? 'Cashier is Closed!' : 'Open Cashier'}}</h1>
  </nav>

  @if($isClosed)
    <h2 class="text-center mt-2">Cashier is already closed for today.</h2>
  @else
    <form class="row mt-4" action="/cashier/open" method="POST">
      <input type="hidden" name="_token" value="{{ csrf_token() }}" />
      <div class="mb-3">
        <label for="cashier" class="form-label">Cashier</label>
        <input type="text" class="form-control" name="cashier" id="cashier" value="{{ $user->firstname . " " . $user->lastname}}" disabled autocomplete="off">
      </div>
      <div class="mb-3">
        <label for="amount" class="form-label">Opening Amount</label>
        <input type="number" class="form-control" name="amount" id="amount" autocomplete="off">
      </div>

      <div class="text-center">
        <button type="submit" id="openCashier" class="btn btn-success" disabled>Open Cashier</button>
      </div>
    </form>
  @endif

  <script>
    $(function() {
      $('#amount').keyup(function() {
        if ($(this).val() > 0) {
          $('#openCashier').removeAttr('disabled');
        } else {
          $('#openCashier').attr('disabled', 'disabled');
        }
      });
    });
  </script>
@endsection