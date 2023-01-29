@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Transactions</h1>

    <form class="row g-3 align-items-center" action="/transactions" method="GET">
      <div class="col-auto">
        <input type="text" class="form-control" placeholder="Search Items in Transactions" name="search" value="{{$search}}" autocomplete="off">
      </div>
      <div class="col-auto">
        <input type="submit" class="form-control btn-outline-success" value="Search"/>
      </div>
    </form>

    <a href="/transaction" class="btn btn-outline-primary">
      Add Transaction
    </a>
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
        <th class="mobile-col-md" scope="col">Transaction Code</th>
        <th scope="col">Total Quantity</th>
        <th scope="col">Total Cost</th>
        <th scope="col">Stock Man</th>
        <th class="mobile-col-md" scope="col">Remarks</th>
        <th scope="col">Date</th>
        <th class="mobile-col-sm" scope="col">Items</th>
      </tr>
    </thead>
    <tbody>
      @if($transactions && count($transactions))
        @foreach($transactions as $transaction)
          <?php 
            $strDate = strtotime($transaction->created_at);
            $transDate = getDate($strDate);
            $transactionDate = $transDate['month']." ".$transDate['mday'].", ".$transDate['year'];
          ?>
          <tr>
            <td class="mobile-col-md">{{ $transaction->transaction_code }}</td>
            <td>{{ $transaction->total_quantity }}</td>
						<td>P{{ number_format($transaction->total_amount) }}</td>
						<td>{{ $transaction->stock_man }}</td>
						<td class="mobile-col-md">{{ $transaction->remarks }}</td>
						<td>{{ $transactionDate }}</td>
            <td class="mobile-col-sm">
              <button 
                class="btn btn-sm btn-outline-primary view-item" 
                data-bs-toggle="modal" 
                data-bs-target="#transactionsModal" 
                data-id="{{ $transaction->id }}" 
                data-json="{{ json_encode($transaction) }}">
                  <i class="fa-regular fa-rectangle-list"></i> View Items
              </button>
            </td>
          </tr>
        @endforeach
      @else
				<tr>
					<th colspan="7" class="text-center">No Transaction found.</th>
				</tr>
      @endif
    </tbody>
    @if($transactions && count($transactions))
      <tfoot>
        <tr>
          <th colspan="12" class="text-center">
            <div class="row g-3 align-items-center">
              <div class="col-auto">
                <label class="col-form-label">Select Page</label>
              </div>
              <div class="col-auto">
                <select class="form-select page-select">
                  @for($i = 1; $i <= $transactions->lastPage(); $i++)
                    <option value="{{ $i }}" {{ $transactions->currentPage() == $i ? 'selected' : '' }}>{{ $i }}</option>
                  @endfor
                </select>
              </div>
            </div>
          </th>
        </tr>
      </tfoot>
    @endif
  </table>

  <div class="modal fade" id="transactionsModal" tabindex="-1" aria-labelledby="transactionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="transactionsModalLabel"><span id="type"></span> Transaction</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Stock Man</label>
            <input disabled class="form-control" name="stockman" id="stockMan"/>
          </div>

          <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" name="remarks" disabled></textarea>
          </div>

          <div class="mb-3 card transaction-items">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">SKU</th>
                  <th scope="col">Item</th>
                  <th scope="col">Supplier</th>
                  <th scope="col">Quantity</th>
                  <th scope="col">Cost</th>
                  <th scope="col">Total Cost</th>
                </tr>
              </thead>
              <tbody id="items">
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    $(function() {
      $('.page-select').change(function() {
        var page = $(this).val();
        location.href = `/transactions?page=${page}`;
      });

      $('.view-item').click(function() {
        var transaction = $(this).data('json');

        $('input[name="stockman"]').attr('disabled', 'disabled').val(transaction.stock_man);
        $('textarea[name="remarks"]').attr('disabled', 'disabled').val(transaction.remarks);

        var html = '';
        transaction.items.map(item => {
          html += `
              <tr>
                <td>${item.item.sku}</td>
                <td>${item.item.name}</td>
                <td>${item.supplier.name}</td>
                <td>${item.quantity}</td>
                <td>${item.amount}</td>
                <td>${item.total_amount}</td>
              </tr>
            `;
        });
        $("#items").html(html);
      });
    });
  </script>
@endsection