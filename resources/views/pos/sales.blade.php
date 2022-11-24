@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Sales</h1>

    <div class="row g-3 align-items-center">
      <div class="col-auto">
        <label for="inputPassword6" class="col-form-label">Sales Date</label>
      </div>
      <div class="col-auto">
        <input type="date" placeholder="Select Date" class="form-control col-auto" id="date"/>
      </div>
    </div>
  </nav>

  @if(\Session::get('error'))
    <div class="alert alert-danger text-center" role="alert">
      {{ \Session::get('error') }}
    </div>
  @endif

  @if(\Session::get('success'))
    <div class="alert alert-success text-center" role="alert">
      {{ \Session::get('success') }}
    </div>
  @endif

  <table class="table">
    <thead>
      <tr>
        <th scope="col">Reference</th>
        <th scope="col">Total Quantity</th>
        <th scope="col">Total Amount</th>
        <th scope="col">Date</th>
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
      @if($sales && count($sales))
        @foreach($sales as $sale)
          <?php 
            $strDate = strtotime($sale->created_at);
            $transDate = getDate($strDate);
          ?>
          <tr>
            <td>{{ $sale->reference }}</td>
						<td>{{ $sale->total_quantity }}</td>
						<td>P{{ number_format($sale->total_amount) }}</td>
						<td>{{ $transDate['month']." ".$transDate['mday'].", ".$transDate['year'] }}</td>
            <td>
              <button 
                class="btn btn-sm btn-outline-primary view-sales" 
                data-bs-toggle="modal" 
                data-bs-target="#salesModal" 
                data-id="{{ $sale->id }}" 
                data-json="{{ json_encode($sale->items) }}">
                <i class="fa-regular fa-rectangle-list"></i>
              </button>
              <a 
                href="/sale/{{ $sale->id }}"
                target="_blank"
                class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-print"></i>
              </a>
            </td>
          </tr>
        @endforeach
      @else
				<tr>
					<th colspan="7" class="text-center">No Sales found.</th>
				</tr>
      @endif
    </tbody>
    @if($sales && count($sales))
      <tfoot>
        <tr>
          <th colspan="12" class="text-center">
            <div class="row g-3 align-items-center">
              <div class="col-auto">
                <label class="col-form-label">Select Page</label>
              </div>
              <div class="col-auto">
                <select class="form-select page-select">
                  @for($i = 1; $i <= $sales->lastPage(); $i++)
                    <option value="{{ $i }}" {{ $sales->currentPage() == $i ? 'selected' : '' }}>{{ $i }}</option>
                  @endfor
                </select>
              </div>
            </div>
          </th>
        </tr>
      </tfoot>
    @endif
  </table>

  <div class="modal fade" id="salesModal" tabindex="-1" aria-labelledby="salesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="salesModalLabel">Items</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <table class="table">
            <thead>
              <tr>
                <th>Item</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total Price</th>
              </tr>
            </thead>
            <tbody id="saleItems">

            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(function() {
      var today = new Date();
      var dateVal = `${today.getFullYear()}-${today.getMonth() + 1}-${today.getDate()}`;
      var inputDate = "{{ $date }}" || dateVal;
      document.getElementById("date").defaultValue = inputDate;

      $('.page-select').change(function() {
        search();
      });

      $('#date').change(function() {
        search(1);
      });

      function search(p, d) {
        var page = p || $('.page-select').val();
        var date = d || $('#date').val();
        location.href = `/sales?page=${page}&date=${date}`;
      }

      $('.view-sales').click(function() {
        $("#saleItems").html("");
        var items = $(this).data('json');
        var html = '';
        items.map(item => {
          html += `
              <tr>
                <td>${item.item.name}</td>
                <td>P${formatMoney(item.amount, 2, '.', ',')}</td>
                <td>${item.quantity}</td>
                <td>P${formatMoney(item.total_amount, 2, '.', ',')}</td>
              </tr>
            `;
        });
        $("#saleItems").html(html);
      });

      function formatMoney(number, decPlaces, decSep, thouSep) {
        decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
        decSep = typeof decSep === "undefined" ? "." : decSep;
        thouSep = typeof thouSep === "undefined" ? "," : thouSep;
        var sign = number < 0 ? "-" : "";
        var i = String(parseInt(number = Math.abs(Number(number) || 0).toFixed(decPlaces)));
        var j = (j = i.length) > 3 ? j % 3 : 0;

        return sign +
            (j ? i.substr(0, j) + thouSep : "") +
            i.substr(j).replace(/(\decSep{3})(?=\decSep)/g, "$1" + thouSep) +
            (decPlaces ? decSep + Math.abs(number - i).toFixed(decPlaces).slice(2) : "");
      }
    });
  </script>
@endsection