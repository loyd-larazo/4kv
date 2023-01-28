@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Daily Sales</h1>
  </nav>

  <table class="table">
    <thead>
      <tr>
        <th scope="col">Date</th>
        <th scope="col">Cashier</th>
        <th scope="col" class="mobile-col-md">Sales Count</th>
        <th scope="col" class="mobile-col-md">Sales Amount</th>
        <th scope="col">Opening Amount</th>
        <th scope="col">Closing Amount</th>
        <th scope="col" class="mobile-col-sm">Discrepancy</th>
      </tr>
    </thead>
    <tbody>
      @if($dailySales && count($dailySales))
        @foreach($dailySales as $sale)
          <?php 
            $strDate = strtotime($sale->created_at);
            $transDate = getDate($strDate);
          ?>
          <tr>
            <td>{{ $transDate['month']." ".$transDate['mday'].", ".$transDate['year'] }}</td>
            <td>
              Opening: {{ $sale->openingUser->firstname }}<br />
              Closing: {{ $sale->closingUser->firstname }}
            </td>
						<td class="mobile-col-md">{{ $sale->sales_count }}</td>
						<td class="mobile-col-md">P{{ number_format($sale->sales_amount) }}</td>
						<td>P{{ number_format($sale->opening_amount) }}</td>
						<td>P{{ number_format($sale->closing_amount) }}</td>
						<td class="mobile-col-sm">P{{ number_format($sale->difference_amount) }}</td>
          </tr>
        @endforeach
      @else
				<tr>
					<th colspan="7" class="text-center">No Sales found.</th>
				</tr>
      @endif
    </tbody>
    @if($dailySales && count($dailySales))
      <tfoot>
        <tr>
          <th colspan="12" class="text-center">
            <div class="row g-3 align-items-center">
              <div class="col-auto">
                <label class="col-form-label">Select Page</label>
              </div>
              <div class="col-auto">
                <select class="form-select page-select">
                  @for($i = 1; $i <= $dailySales->lastPage(); $i++)
                    <option value="{{ $i }}" {{ $dailySales->currentPage() == $i ? 'selected' : '' }}>{{ $i }}</option>
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
    $(function() {
  
    });
  </script>
@endsection