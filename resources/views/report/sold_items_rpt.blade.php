@extends('report.report_layout')

@section('content')
<div class="mb-12">
  <div class="px-4">

    <div class="navbar navbar-light text-center d-flex justify-content-center">
      <h3>Sold Items Report</h3>
    </div>
    
    <div class="">
      <table class="table table-bordered mb-4 table-rpt">
        <thead>
          <tr>
            @if (in_array(1, $cols))<th>SKU</th>@endif
            @if (in_array(2, $cols))<th>Item</th>@endif
            @if (in_array(3, $cols))<th>Price</th>@endif
            @if (in_array(4, $cols))<th>Sold</th>@endif
            @if (in_array(5, $cols))<th>Total Price</th>@endif
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $item)
            <?php
              $price = number_format((float)$item->item->price, 2, '.', ',');
              $totalPrice = number_format((float)$item->total_price, 2, '.', ',');
            ?>
            <tr>
              @if (in_array(1, $cols))<td>{{ $item->item->sku }}</td>@endif
              @if (in_array(2, $cols))<td>{{ $item->item->name }}</td>@endif
              @if (in_array(3, $cols))<td>P{{ $price }}</td>@endif
              @if (in_array(4, $cols))<td>{{ $item->sold }}</td>@endif
              @if (in_array(5, $cols))<td>P{{ $totalPrice }}</td>@endif
            </tr>
          @endforeach
          <?php
            $grandPrice = number_format((float)$grandTotal['price'], 2, '.', ',');
            $grandTotalPrice = number_format((float)$grandTotal['total_price'], 2, '.', ',');
          ?>
          <tr>
            @if (in_array(1, $cols))<td></td>@endif
            @if (in_array(2, $cols))<td></td>@endif
            @if (in_array(3, $cols))<td>P{{ $grandPrice }}</td>@endif
            @if (in_array(4, $cols))<td></td>@endif
            @if (in_array(5, $cols))<td>P{{ $grandTotalPrice }}</td>@endif
          </tr>
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection 