@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Transactions</h1>

    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#transactionsModal" id="addTransaction">
      Add Transaction
    </button>
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
        <th scope="col">Transaction Code</th>
        <th scope="col">Total Quantity</th>
        <th scope="col">Total Cost</th>
        <th scope="col">Laborer</th>
        <th scope="col">Remarks</th>
        <th scope="col">Date</th>
        <th scope="col">Items</th>
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
            <td>{{ $transaction->transaction_code }}</td>
            <td>{{ $transaction->total_quantity }}</td>
						<td>P{{ number_format($transaction->total_amount) }}</td>
						<td>{{ $transaction->laborer ? $transaction->laborer->firstname." ".$transaction->laborer->lastname : '' }}</td>
						<td>{{ $transaction->remarks }}</td>
						<td>{{ $transactionDate }}</td>
            <td>
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
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

        <div class="modal-header">
          <h5 class="modal-title" id="transactionsModalLabel"><span id="type"></span> Transaction</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          @if(isset($laborers))
            <div class="mb-3">
              <label class="form-label">Laborer</label>
              <select class="form-select" name="laborer" required>
                <option value="">Select Laborer</option>
                @foreach($laborers as $laborer)
                  <option value="{{ $laborer->id }}">{{ $laborer->firstname." ".$laborer->lastname }}</option>
                @endforeach			
              </select>
            </div>
          @endif

          <div class="mb-3">
            <label class="form-label">Remarks</label>
            <textarea class="form-control" name="remarks" required></textarea>
          </div>

          <div class="mb-3" id="scanItems">
            <label class="form-label">Scan Barcode or enter SKU</label>
            <div class="row m-0 p-0">
              <div class="col-5 p-0">
                <input type="text" class="form-control" placeholder="SKU" id="sku"/>
              </div>
              <div class="col-7 fw-bold px-0">
                @if(isset($suppliers))
                  <select class="form-select" name="supplier" id="supplier" required>
                    <option value="">Select Supplier</option>
                    @foreach(json_decode($suppliers) as $supplier)
                      <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach			
                  </select>
                @endif
              </div>
            </div>
            <div class="row m-0 p-0">
              <div class="col-5 p-0 mt-2">
                <input type="number" class="form-control" placeholder="Quantity" id="quantity"/>
              </div>
              <div class="col-5 fw-bold mt-2 px-0">
                <input type="number" class="form-control" placeholder="Cost/Item" id="cost"/>
              </div>
              <div class="col-2 fw-bold mt-2 px-0">
                <button class="btn btn-outline-primary" id="addItem">
                  <i class="fa-regular fa-square-plus"></i> Item
                </button>
              </div>
            </div>
            <div class="row m-0 p-0">
              <div class="col-12 fw-bold mt-2">
                SKU: <span id="searchedSku"></span>
              </div>
              <div class="col-12 fw-bold mt-2">
                Name: <span id="searchedName"></span>
              </div>
            </div>
          </div>

          <div class="mb-3 card transaction-items">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">SKU</th>
                  <th scope="col">Item</th>
                  <th scope="col">Supplier</th>
                  <th scope="col">Cost</th>
                  <th scope="col">Quantity</th>
                </tr>
              </thead>
              <tbody id="items">
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-outline-success" id="submitTransaction">Save</button>
        </div>
      </div>
    </div>
  </div>
  
  <script>
    $(function() {
      var productItems = JSON.parse(@json($items));
      var suppliers = JSON.parse(@json($suppliers));
      var addedItems = [];

      $('#addTransaction').click(function() {
        $('#type').html("Add");

        $("#items").html("");
        $('#scanItems').show();
        $('select[name="laborer"]').removeAttr('disabled');
        $('textarea[name="remarks"]').removeAttr('disabled');
        $('.modal-footer').show();
      });

      $('.page-select').change(function() {
        var page = $(this).val();
        location.href = `/transactions?page=${page}`;
      });

      $('#sku').keyup(function() {
        var sku = $(this).val();
        var selectedItem = productItems.find(pItem => pItem.sku == sku);

        if (selectedItem) {
          $('#searchedSku').html(selectedItem.sku);
          $('#searchedName').html(selectedItem.name);
        } else {
          $('#searchedSku').html("Not found");
          $('#searchedName').html("Not found");
        }
      });

      $('#addItem').click(function() {
        var sku = $("#sku").val();
        var quantity = $("#quantity").val();
        var cost = $("#cost").val();
        var supplier = $("#supplier").val();
        
        var selectedItem = productItems.find(pItem => pItem.sku == sku);

        if (selectedItem && quantity && supplier && cost) {
          var itemIndex = addedItems.findIndex(ai => (ai.sku == sku && ai.supplier == supplier));
          if (itemIndex >= 0) {
            var addedItem = addedItems[itemIndex];
            addedItem.quantity = parseInt(addedItem.quantity) + parseInt(quantity);
            addedItem.cost = parseInt(cost);
          } else {
            selectedItem.quantity = parseInt(quantity);
            selectedItem.cost = parseInt(cost);
            selectedItem.supplier = parseInt(supplier);
            addedItems.push(selectedItem);
          }

          var html = "";
          addedItems.map(aItem => {
            var selectedSupp = suppliers.find(supp => supp.id == supplier);
            html += `
              <tr>
                <td>${aItem.sku}</td>
                <td>${aItem.name}</td>
                <td>${selectedSupp.name}</td>
                <td>${aItem.cost}</td>
                <td>${aItem.quantity}</td>
              </tr>
            `;
          });
          $("#items").html(html);
          $("#supplier").val("");
          $("#sku").val("").focus();
          $("#quantity").val("");
          $("#cost").val("");
        }
      });

      $('#submitTransaction').click(function() {
        var laborer = $('select[name="laborer"]').val();
        var remarks = $('textarea[name="remarks"]').val();

        var form = document.createElement("form");

        form.method = "POST";
        form.action = "/transaction";
        form.style.cssText = "display:none;"

        var tokenForm = document.createElement("input");
        tokenForm.value = "{{ csrf_token() }}";
        tokenForm.name = "_token";
        form.appendChild(tokenForm);  

        var laborerForm = document.createElement("input");
        laborerForm.value = laborer;
        laborerForm.name = "laborer";
        form.appendChild(laborerForm);  

        var remarkForm = document.createElement("input");
        remarkForm.value = remarks;
        remarkForm.name = "remarks";
        form.appendChild(remarkForm);

        var itemsForm = document.createElement("input");
        itemsForm.value = JSON.stringify(addedItems);
        itemsForm.name = "items";
        form.appendChild(itemsForm);

        document.body.appendChild(form);
        form.submit();
      });

      $('.view-item').click(function() {
        var transaction = $(this).data('json');

        $('#scanItems').hide();
        $('.modal-footer').hide();
        $('select[name="laborer"]').attr('disabled', 'disabled').val(transaction.laborer_id);
        $('textarea[name="remarks"]').attr('disabled', 'disabled').val(transaction.remarks);

        var html = '';
        transaction.items.map(item => {
          html += `
              <tr>
                <td>${item.item.sku}</td>
                <td>${item.item.name}</td>
                <td>${item.supplier.name}</td>
                <td>${item.amount}</td>
                <td>${item.quantity}</td>
              </tr>
            `;
        });
        $("#items").html(html);
      });
    });
  </script>
@endsection