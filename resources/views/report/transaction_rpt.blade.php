@extends('report.report_layout')

@section('content')
<div class="mb-12">
  <div class="px-4">

    <div class="navbar navbar-light text-center d-flex justify-content-center">
      <h3>Transaction List Report</h3>
    </div>
    
    <div class="">
      <table class="table table-bordered mb-4 table-rpt">
        <thead>
          <tr>
            @if (in_array(1, $cols))<th>Transaction Code</th>@endif
            @if (in_array(2, $cols))<th>Total Quantity</th>@endif
            @if (in_array(3, $cols))<th>Total Cost</th>@endif
            @if (in_array(4, $cols))<th>Stock Man</th>@endif
            @if (in_array(5, $cols))<th>Remarks</th>@endif
            @if (in_array(6, $cols))<th>Date</th>@endif
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $item)
            <?php
              $amount = number_format((float)$item->total_amount, 2, '.', ',');
              $date = date("F j, Y", strtotime($item->created_at));
            ?>
            <tr>
              @if (in_array(1, $cols))<td>{{ $item->transaction_code }}</td>@endif
              @if (in_array(2, $cols))<td>{{ $item->total_quantity }}</td>@endif
              @if (in_array(3, $cols))<td>P{{ $amount }}</td>@endif
              @if (in_array(4, $cols))<td>{{ $item->stock_man }}</td>@endif
              @if (in_array(5, $cols))<td>{{ $item->remarks }}</td>@endif
              @if (in_array(6, $cols))<td>{{ $date }}</td>@endif
            </tr>
          @endforeach
          <?php
            $grandAmount = number_format((float)$grandTotal['total_amount'], 2, '.', ',');
          ?>
          <tr>
            @if (in_array(1, $cols))<td></td>@endif
            @if (in_array(2, $cols))<td></td>@endif
            @if (in_array(3, $cols))<td>P{{ $grandAmount }}</td>@endif
            @if (in_array(4, $cols))<td></td>@endif
            @if (in_array(5, $cols))<td></td>@endif
            @if (in_array(6, $cols))<td></td>@endif
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection 