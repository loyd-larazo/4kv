@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light px-3">
    <h1>Dashboard</h1>

    <span class="fw-bold">
      Today: {{ date('Y').'/'.date('m').'/'.date('d') }} <span id="time"></span>
    </span>
  </nav>

  <div class="row justify-content-end mx-0 px-0">
    <div class="col-12 col-lg-3 align-self-end row mx-0 px-0">
      <label class="col-auto pt-2">Filter Report: </label>
      <select class="form-control col" id="filterReport">
        <option {{$reportBy == "Daily" ? "selected" : ""}} value="Daily">Daily</option>
        <option {{$reportBy == "Weekly" ? "selected" : ""}} value="Weekly">Weekly</option>
        <option {{$reportBy == "Monthly" ? "selected" : ""}} value="Monthly">Monthly</option>
        <option {{$reportBy == "Quarterly" ? "selected" : ""}} value="Quarterly">Quarterly</option>
        <option {{$reportBy == "Yearly" ? "selected" : ""}} value="Yearly">Yearly</option>
      </select>
    </div>
  </div>
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
      const reportBy = "{{$reportBy}}";
      setInterval(function() {
        const d = new Date();
        $('#time').html(`${d.getHours()}:${d.getMinutes()}:${d.getSeconds()}`);
      }, 1000);

      $('#filterReport').change(function() {
        var type = $(this).val();
        location.href=`/?reportBy=${type}`;
      });
      const today = new Date();

      const weeks = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
      const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
      const quarters = [
        ["January", "February", "March"],
        ["April", "May", "June"],
        ["July", "August", "September"],
        ["October", "November", "December"],
      ];
      const currentMonthI = (today.getMonth() + 1);
      const currentQuarter = quarters[Math.ceil(currentMonthI/3) - 1];
      var currentYear = today.getFullYear();
      var years = [];
      const toYear = currentYear - 5;
      for (currentYear; currentYear >= toYear; currentYear--) {
        years.push(currentYear);
      }

      const xAxisLabel = {
        Daily: "Last 30 days",
        Weekly: `${months[today.getMonth()]} Sales`,
        Monthly: "Months of the Year",
        Quarterly: `${today.getFullYear()} Quarterly Report`,
        Yearly: "Years"
      };

      renderReport();
      
      function renderReport() {
        var sales = JSON.parse(@json($sales));

        // Sort Monthly reports
        if (reportBy == "Monthly") {
          var salesToSort = {};
          var finalSales = [];
          sales.map(sale => {
            var monthlyDate = sale.label.split(" ");
            sale.month = parseInt(monthlyDate[0]);
            sale.year = parseInt(monthlyDate[1]);

            if (salesToSort[sale.year]) {
              salesToSort[sale.year].push(sale);
            } else {
              salesToSort[sale.year] = [sale];
            }
          });

          Object.keys(salesToSort).map(sts => {
            var toConcat = salesToSort[sts].sort((a, b) => a.month > b.month ? 1 : -1);
            finalSales = finalSales.concat(toConcat);
          });
          sales = finalSales
        }

        // Replace months to string
        if (["Monthly", "Daily"].indexOf(reportBy) >= 0) {
          sales.map(sale => {
            var labelArr = sale.label.split(" ");
            labelArr[0] = months[parseInt(labelArr[0]) - 1];
            sale.label = labelArr.join(" ");
          });
        }

        if (sales) {
          var options = {
            animationEnabled: true,
            title: {
              text: `${reportBy} Sales`
            },
            axisY: {
              title: "Sales Amount",
              suffix: " amount"
            },
            axisX: {
              title: xAxisLabel[reportBy]
            },
            data: [{
              type: "column",
              yValueFormatString: "#,##0.0#"%"",
              dataPoints: [...sales]
            }]
          };
          $("#chartContainer").CanvasJSChart(options);
        }
      }

      $('.page-select').change(function() {
        var page = $(this).val();
        location.href = `/?page=${page}`;
      });
    });
  </script>
@endsection