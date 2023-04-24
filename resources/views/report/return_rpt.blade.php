@extends('report.report_layout')

@section('content')
<div class="mb-12">
  <div class="px-4">

    <div class="navbar navbar-light text-center d-flex justify-content-center">
      <h3>Return Purchases Report</h3>
    </div>
    
    <div class="">
      <table class="table table-bordered mb-4 table-rpt">
        <thead>
          <tr>
            @if (in_array(1, $cols))<th>Transaction Code</th>@endif
            @if (in_array(2, $cols))<th>Returned By Firstname</th>@endif
            @if (in_array(3, $cols))<th>Returned By Lastname</th>@endif
            @if (in_array(4, $cols))<th>Total Quantity</th>@endif
            @if (in_array(5, $cols))<th>Total Amount</th>@endif
            @if (in_array(6, $cols))<th>Status</th>@endif
            @if (in_array(7, $cols))<th>Date</th>@endif
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $item)
            <?php
              $user = $item->user;
              $fname = (isset($user) && $user->firstname) ? $user->firstname : '';
              $lname = (isset($user) && $user->lastname) ? $user->lastname : '';

              $totalAmount = number_format((float)$item->total_amount, 2, '.', ',');

              $date = date("F j, Y", strtotime($item->created_at));
            ?>
            <tr>
              @if (in_array(1, $cols))<td>{{ $item->transaction->transaction_code }}</td>@endif
              @if (in_array(2, $cols))<td>{{ $fname }}</td>@endif
              @if (in_array(3, $cols))<td>{{ $lname }}</td>@endif
              @if (in_array(4, $cols))<td>{{ $item->quantity }}</td>@endif
              @if (in_array(5, $cols))<td>P{{ $totalAmount }}</td>@endif
              @if (in_array(6, $cols))<td>{{ $item->status }}</td>@endif
              @if (in_array(7, $cols))<td>{{ $date }}</td>@endif
            </tr>
          @endforeach
          <?php
            $grandTotalPrice = number_format((float)$grandTotal['total_amount'], 2, '.', ',');
          ?>
          <tr>
            @if (in_array(1, $cols))<th></th>@endif
            @if (in_array(2, $cols))<th></th>@endif
            @if (in_array(3, $cols))<th></th>@endif
            @if (in_array(4, $cols))<th></th>@endif
            @if (in_array(5, $cols))<th>P{{ $grandTotalPrice }}</th>@endif
            @if (in_array(6, $cols))<th></th>@endif
            @if (in_array(7, $cols))<th></th>@endif
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection 