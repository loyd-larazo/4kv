@extends('report.report_layout')

@section('content')
<div class="mb-12">
  <div class="px-4">

    <div class="navbar navbar-light text-center d-flex justify-content-center">
      <h3>Discarded Items Report</h3>
    </div>
    
    <div class="">
      <table class="table table-bordered mb-4 table-rpt">
        <thead>
          <tr>
            @if (in_array(1, $cols))<th>SKU</th>@endif
            @if (in_array(2, $cols))<th>Item</th>@endif
            @if (in_array(3, $cols))<th>Discarded By Firstname</th>@endif
            @if (in_array(4, $cols))<th>Discarded By Lastname</th>@endif
            @if (in_array(5, $cols))<th>Supplier</th>@endif
            @if (in_array(6, $cols))<th>Amount</th>@endif
            @if (in_array(7, $cols))<th>Quantity</th>@endif
            @if (in_array(8, $cols))<th>Total Amount</th>@endif
            @if (in_array(9, $cols))<th>Date</th>@endif
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $item)
            <?php
              $user = $item->user;
              $fname = (isset($user) && $user->firstname) ? $user->firstname : '';
              $lname = (isset($user) && $user->lastname) ? $user->lastname : '';

              $amount = number_format((float)$item->amount, 2, '.', ',');
              $totalAmount = number_format((float)$item->total_amount, 2, '.', ',');

              $date = date("F j, Y", strtotime($item->created_at));
            ?>
            <tr>
              @if (in_array(1, $cols))<td>{{ $item->item->sku }}</td>@endif
              @if (in_array(2, $cols))<td>{{ $item->item->name }}</td>@endif
              @if (in_array(3, $cols))<td>{{ $fname }}</td>@endif
              @if (in_array(4, $cols))<td>{{ $lname }}</td>@endif
              @if (in_array(5, $cols))<td>{{ $item->supplier->name }}</td>@endif
              @if (in_array(6, $cols))<td>P{{ $amount }}</td>@endif
              @if (in_array(7, $cols))<td>{{ $item->quantity }}</td>@endif
              @if (in_array(8, $cols))<td>P{{ $totalAmount }}</td>@endif
              @if (in_array(9, $cols))<td>{{ $date }}</td>@endif
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
            @if (in_array(5, $cols))<th></th>@endif
            @if (in_array(6, $cols))<th></th>@endif
            @if (in_array(7, $cols))<th></th>@endif
            @if (in_array(8, $cols))<th>P{{ $grandTotalPrice }}</th>@endif
            @if (in_array(9, $cols))<th></th>@endif
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection 