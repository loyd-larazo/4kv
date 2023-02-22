@extends('report.report_layout')

@section('content')
<div class="mb-12">
  <div class="px-4">

    <div class="navbar navbar-light text-center d-flex justify-content-center">
      <h3>Daily Sales Report</h3>
    </div>
    
    <div class="">
      <table class="table table-bordered mb-4 table-rpt">
        <thead>
          <tr>
            @if (in_array(1, $cols))<th>Date</th>@endif
            @if (in_array(2, $cols))<th>Opening Cashier <br>Firstname</th>@endif
            @if (in_array(3, $cols))<th>Opening Cashier <br>Lastname</th>@endif
            @if (in_array(4, $cols))<th>Closing Cashier <br>Firstname</th>@endif
            @if (in_array(5, $cols))<th>Closing Cashier <br>Lastname</th>@endif
            @if (in_array(6, $cols))<th>Sales Count</th>@endif
            @if (in_array(7, $cols))<th>Sales Amount</th>@endif
            @if (in_array(8, $cols))<th>Opening Amount</th>@endif
            @if (in_array(9, $cols))<th>Closing Amount</th>@endif
            @if (in_array(10, $cols))<th>Discrepancy</th>@endif
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $item)
            <?php
              $date = date("F j, Y", strtotime($item->created_at));
              $salesAmount = number_format((float)$item->sales_amount, 2, '.', ',');
              $openingAmount = number_format((float)$item->opening_amount, 2, '.', ',');
              $closingAmount = number_format((float)$item->closing_amount, 2, '.', ',');
              $diff = number_format((float)$item->difference_amount, 2, '.', ',');

              $ou = $item->openingUser;
              $cu = $item->closingUser;
              $oFname = (isset($ou) && $ou->firstname) ? $ou->firstname : '';
              $oLname = (isset($ou) && $ou->lastname) ? $ou->lastname : '';
              $cFname = (isset($cu) && $cu->firstname) ? $cu->firstname : '';
              $cLname = (isset($cu) && $cu->lastname) ? $cu->lastname : '';
            ?>
            <tr>
              @if (in_array(1, $cols))<td>{{ $date }}</td>@endif
              @if (in_array(2, $cols))<td>{{ $oFname }}</td>@endif
              @if (in_array(3, $cols))<td>{{ $oLname }}</td>@endif
              @if (in_array(4, $cols))<td>{{ $cFname }}</td>@endif
              @if (in_array(5, $cols))<td>{{ $cLname }}</td>@endif
              @if (in_array(6, $cols))<td>{{ $item->sales_count }}</td>@endif
              @if (in_array(7, $cols))<td>P{{ $salesAmount }}</td>@endif
              @if (in_array(8, $cols))<td>P{{ $openingAmount }}</td>@endif
              @if (in_array(9, $cols))<td>P{{ $closingAmount }}</td>@endif
              @if (in_array(10, $cols))<td>{{ $diff }}</td>@endif
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection 