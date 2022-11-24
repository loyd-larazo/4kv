@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light px-3">
    <h1>Dashboard</h1>

    <span class="fw-bold">
      Today: {{ date('Y').'/'.date('m').'/'.date('d') }} <span id="time"></span>
    </span>
  </nav>

  <div id="chartContainer" style="height: 370px; width: 100%;"></div>

  <div class="mt-5">
    <h4>Top Selling Items:</h4>
    <table class="table">
      <thead>
        <tr>
          <th scope="col">SKU</th>
          <th scope="col">Product</th>
          <th scope="col">Sold</th>
        </tr>
      </thead>
      <tbody>
        @if($topSelling && count($topSelling))
          @foreach($topSelling as $topSell)
            <tr>
              <td>{{ $topSell->item->sku }}</td>
              <td>{{ $topSell->item->name }}</td>
              <td>{{ $topSell->sold }}</td>
            </tr>
          @endforeach
        @else
          <tr>
            <th colspan="7" class="text-center">No items found.</th>
          </tr>
        @endif
      </tbody>
    </table>
  </div>

  <div class="mt-5">
    <h4>Low Stock Items: {{ $lowStocks->total() }}</h4>
    <table class="table">
      <thead>
        <tr>
          <th scope="col">SKU</th>
          <th scope="col">Product</th>
          <th scope="col">Stock</th>
        </tr>
      </thead>
      <tbody>
        @if($lowStocks && count($lowStocks))
          @foreach($lowStocks as $item)
            <tr>
              <td>{{ $item->sku }}</td>
              <td>{{ $item->name }}</td>
              <td>{{ $item->stock }}</td>
            </tr>
          @endforeach
        @else
          <tr>
            <th colspan="7" class="text-center">No items found.</th>
          </tr>
        @endif
      </tbody>
      @if($lowStocks && count($lowStocks))
        <tfoot>
          <tr>
            <th colspan="12" class="text-center">
              <div class="row g-3 align-items-center">
                <div class="col-auto">
                  <label class="col-form-label">Select Page</label>
                </div>
                <div class="col-auto">
                  <select class="form-select page-select">
                    @for($i = 1; $i <= $lowStocks->lastPage(); $i++)
                      <option value="{{ $i }}" {{ $lowStocks->currentPage() == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                  </select>
                </div>
              </div>
            </th>
          </tr>
        </tfoot>
      @endif
    </table>
  </div>

  <script>
    $(function() {
      var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

      setInterval(function() {
        const d = new Date();
        $('#time').html(`${d.getHours()}:${d.getMinutes()}:${d.getSeconds()}`);
      }, 1000);

      var sales = JSON.parse(@json($sales));

      sales.map(sale => {
        var labelArr = sale.label.split(" ");
        labelArr[0] = months[parseInt(labelArr[0])];
        sale.label = labelArr.join(" ");
      });
      if (sales) {
        var options = {
          animationEnabled: true,
          title: {
            text: "Monthly Sales"
          },
          axisY: {
            title: "Item Sold",
            suffix: " items"
          },
          axisX: {
            title: "Month"
          },
          data: [{
            type: "column",
            yValueFormatString: "#,##0.0#"%"",
            dataPoints: [...sales]
          }]
        };
        $("#chartContainer").CanvasJSChart(options);
      }

      $('.page-select').change(function() {
        var page = $(this).val();
        location.href = `/?page=${page}`;
      });
    });
  </script>
@endsection