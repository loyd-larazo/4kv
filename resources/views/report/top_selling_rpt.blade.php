@extends('report.report_layout')

@section('content')
<div class="mb-12">
  <div class="px-4">

    <div class="navbar navbar-light text-center d-flex justify-content-center">
      <h3>Top Selling Items Report</h3>
    </div>
    
    <div class="">
      <table class="table table-bordered mb-4 table-rpt">
        <thead>
          <tr>
            @if (in_array(1, $cols))<th>SKU</th>@endif
            @if (in_array(2, $cols))<th>Item</th>@endif
            @if (in_array(3, $cols))<th>Sold</th>@endif
          </tr>
        </thead>
        <tbody>
          @foreach ($data as $item)
            <tr>
              @if (in_array(1, $cols))<td>{{ $item->item->sku }}</td>@endif
              @if (in_array(2, $cols))<td>{{ $item->item->name }}</td>@endif
              @if (in_array(3, $cols))<td>{{ $item->sold }}</td>@endif
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection 