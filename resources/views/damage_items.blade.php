@extends('app')

@section('content')
<nav class="navbar navbar-light bg-light">
  <h1>Damage Items</h1>
</nav>

@if(\Session::get('error'))
  <div class="alert alert-danger text-center" role="alert">
    {{ \Session::get('error') }}
  </div>
@endif

@if(\Session::get('success'))
  <div class="alert alert-success text-center mt-2" role="alert">
    {{ \Session::get('success') }}
  </div>
@endif

<table class="table">
  <thead>
    <tr>
      <th scope="col">Item</th>
      <th scope="col">Price</th>
      <th scope="col">Quantity</th>
      <th scope="col">Total Price</th>
    </tr>
  </thead>
  <tbody>
    @if(isset($damages) && count($damages))
      @foreach($damages as $damage)
        <tr>
          <td>{{ $damage->item->name }}</td>
          <td>P{{ number_format($damage->amount) }}</td>
          <td>{{ $damage->quantity }}</td>
          <td>P{{ number_format($damage->total_amount) }}</td>
        </tr>
      @endforeach
    @else
      <tr>
        <th colspan="7" class="text-center">No return found.</th>
      </tr>
    @endif
  </tbody>
  @if(isset($damages) && count($damages))
    <tfoot>
      <tr>
        <th colspan="12" class="text-center">
          <div class="row g-3 align-items-center">
            <div class="col-auto">
              <label class="col-form-label">Select Page</label>
            </div>
            <div class="col-auto">
              <select class="form-select page-select">
                @for($i = 1; $i <= $damages->lastPage(); $i++)
                  <option value="{{ $i }}" {{ $damages->currentPage() == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
              </select>
            </div>
          </div>
        </th>
      </tr>
    </tfoot>
  @endif
</table>

<script>

</script>
@endsection