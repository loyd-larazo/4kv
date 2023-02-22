@extends('report.report_layout')

@section('content')
<div class="mb-12">
  <div class="px-4">

    <div class="navbar navbar-light text-center d-flex justify-content-center">
      <h3>Inventory List Report</h3>
    </div>
    
    <div class="">
      <table class="table table-bordered mb-4 table-rpt">
        <thead>
          <tr>
            @if (in_array(1, $cols))<th>SKU</th>@endif
            @if (in_array(2, $cols))<th>Item</th>@endif
            @if (in_array(3, $cols))<th>Cost</th>@endif
            @if (in_array(4, $cols))<th>Price</th>@endif
            @if (in_array(5, $cols))<th>Description</th>@endif
            @if (in_array(6, $cols))<th>Category</th>@endif
            @if (in_array(7, $cols))<th>Sold Item by Length</th>@endif
            @if (in_array(8, $cols))<th>Sold Item by Weight</th>@endif
            @if (in_array(9, $cols))<th>Stock</th>@endif
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $item)
            <?php
              $cost = number_format((float)$item->cost, 2, '.', ',');
              $price = number_format((float)$item->price, 2, '.', ',');
              $byLength = ($item->sold_by_length ? 'Yes' : 'No');
              $byWeight = ($item->sold_by_weight ? 'Yes' : 'No');
              $category = (isset($item->category) && $item->category->name) ? $item->category->name : '';
              $description = ($item->description) ? $item->description : '';
            ?>
            <tr>
              @if (in_array(1, $cols))<td>{{ $item->sku }}</td>@endif
              @if (in_array(2, $cols))<td>{{ $item->name }}</td>@endif
              @if (in_array(3, $cols))<td>P{{ $cost }}</td>@endif
              @if (in_array(4, $cols))<td>P{{ $price }}</td>@endif
              @if (in_array(5, $cols))<td>{{ $description }}</td>@endif
              @if (in_array(6, $cols))<td>{{ $category }}</td>@endif
              @if (in_array(7, $cols))<td>{{ $byLength }}</td>@endif
              @if (in_array(8, $cols))<td>{{ $byWeight }}</td>@endif
              @if (in_array(9, $cols))<td>{{ $item->stock }}</td>@endif
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection 