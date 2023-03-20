@extends('report.report_layout')

@section('content')
<div class="mb-12">
  <div class="px-4">

    <div class="navbar navbar-light text-center d-flex justify-content-center">
      <h3>Sales List Report</h3>
    </div>
    
    <div class="">
      <table class="table table-bordered mb-4 table-rpt">
        <thead>
          <tr>
            @if (in_array(1, $cols))<th>Reference</th>@endif
            @if (in_array(2, $cols))<th>Cashier Firstname</th>@endif
            @if (in_array(3, $cols))<th>Cashier Lastname</th>@endif
            @if (in_array(4, $cols))<th>Total Quantity</th>@endif
            @if (in_array(5, $cols))<th>Total Discount</th>@endif
            @if (in_array(6, $cols))<th>Total Amount</th>@endif
            @if (in_array(7, $cols))<th>Date</th>@endif
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $item)
            <?php
              $date = date("F j, Y", strtotime($item->created_at));

              $user = $item->user;
              $fname = (isset($user) && $user->firstname) ? $user->firstname : '';
              $lname = (isset($user) && $user->lastname) ? $user->lastname : '';

              $discount = number_format((float)$item->total_discount, 2, '.', ',');
              $amount = number_format((float)$item->total_amount, 2, '.', ',');
            ?>
            <tr>
              @if (in_array(1, $cols))<td>{{ $item->reference }}</td>@endif
              @if (in_array(2, $cols))<td>{{ $fname }}</td>@endif
              @if (in_array(3, $cols))<td>{{ $lname }}</td>@endif
              @if (in_array(4, $cols))<td>{{ $item->total_quantity }}</td>@endif
              @if (in_array(5, $cols))<td>P{{ $discount }}</td>@endif
              @if (in_array(6, $cols))<td>P{{ $amount }}</td>@endif
              @if (in_array(7, $cols))<td>{{ $date }}</td>@endif
            </tr>
          @endforeach
          <?php
            $grandDiscount = number_format((float)$grandTotal['total_discount'], 2, '.', ',');
            $grandAmount = number_format((float)$grandTotal['total_amount'], 2, '.', ',');
          ?>
          <tr>
            @if (in_array(1, $cols))<td></td>@endif
            @if (in_array(2, $cols))<td></td>@endif
            @if (in_array(3, $cols))<td></td>@endif
            @if (in_array(4, $cols))<td></td>@endif
            @if (in_array(5, $cols))<td>P{{ $grandDiscount }}</td>@endif
            @if (in_array(6, $cols))<td>P{{ $grandAmount }}</td>@endif
            @if (in_array(7, $cols))<td></td>@endif
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection 