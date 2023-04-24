@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Return Purchased</h1>

    <form class="row g-3 align-items-center" action="/return-transactions" method="GET">
      <div class="col-auto">
        <div class="form-control clear-input">
          <input type="text" class="form-control" placeholder="Search Return Purchased" name="search" value="{{$search}}" autocomplete="off">
          @if(isset($search) && $search != '')
            <button class="btn btn-sm btn-light" id="clear-search">
              <i class="fa fa-times-circle"></i>
            </button>
          @endif
        </div>
      </div>
      <div class="col-auto">
        <input type="submit" class="form-control btn-outline-success" value="Search" autocomplete="off"/>
      </div>
    </form>

    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#returnModal" id="returnTransaction">
      Return Purchase
    </button>
  </nav>

  @if(\Session::get('error'))
    <div class="alert alert-danger text-center" role="alert">
      {{ \Session::get('error') }}
    </div>
  @endif

  @if(\Session::get('success'))
    <div class="alert alert-success text-center mt-2" role="alert">
      {{ \Session::get('success') }}
    </div>
  @endif

  <table class="table">
    <thead>
      <tr>
        <th scope="col">Transaction Code</th>
        <th scope="col">Returned By</th>
        <th scope="col">Total Quantity</th>
        <th scope="col">Total Amount</th>
        <th scope="col">Date Created</th>
        <th scope="col">Status</th>
        <th scope="col">Items</th>
      </tr>
    </thead>

    <tbody>
      @if(isset($returnTransactions) && count($returnTransactions))
        @foreach($returnTransactions as $returnTransaction)
          <?php
            $fname = $returnTransaction->user->firstname ? $returnTransaction->user->firstname : '';
            $lname = $returnTransaction->user->lastname ? $returnTransaction->user->lastname : '';
            $returnedBy = $fname." ".$lname;

            $strDate = strtotime($returnTransaction->created_at);
            $transDate = getDate($strDate);
            $returnedDate = $transDate['month']." ".$transDate['mday'].", ".$transDate['year'];
          ?>
          <tr>
            <td class="mobile-col-md">{{ $returnTransaction->transaction->transaction_code }}</td>
            <td class="mobile-col-md">{{ $returnedBy }}</td>
            <td>{{ $returnTransaction->quantity }}</td>
            <td>P{{ $returnTransaction->total_amount }}</td>
            <td>{{ $returnedDate }}</td>
            <td class="mobile-col-sm">
              {{ $returnTransaction->status }}
            </td>
            <td class="mobile-col-sm">
              @if($returnTransaction->status === 'pending')
                <button 
                  class="btn btn-sm btn-outline-success pickup"
                  data-id="{{ $returnTransaction->id }}" 
                  data-json="{{ json_encode($returnTransaction) }}">
                    <i class="fas fa-truck-pickup"></i> Pick Up
                </button>
                <button 
                  class="btn btn-sm btn-outline-danger discard" 
                  data-bs-toggle="modal" 
                  data-bs-target="#discardModal" 
                  data-id="{{ $returnTransaction->id }}" 
                  data-json="{{ json_encode($returnTransaction) }}">
                    <i class="fas fa-trash"></i> Discard
                </button>
              @endif
              <button 
                class="btn btn-sm btn-outline-primary view-item" 
                data-bs-toggle="modal" 
                data-bs-target="#itemsModal" 
                data-id="{{ $returnTransaction->id }}" 
                data-json="{{ json_encode($returnTransaction) }}">
                  <i class="fa-regular fa-rectangle-list"></i> View
              </button>
            </td>
          </tr>
        @endforeach
      @else
        <tr>
          <th colspan="7" class="text-center">No returned purchases found.</th>
        </tr>
      @endif
    </tbody>

    @if(isset($returnTransactions) && count($returnTransactions))
      <tfoot>
        <tr>
          <th colspan="12" class="text-center">
            <div class="row g-3 align-items-center">
              <div class="col-auto">
                <label class="col-form-label">Select Page</label>
              </div>
              <div class="col-auto">
                <select class="form-select page-select">
                  @for($i = 1; $i <= $returnTransactions->lastPage(); $i++)
                    <option value="{{ $i }}" {{ $returnTransactions->currentPage() == $i ? 'selected' : '' }}>{{ $i }}</option>
                  @endfor
                </select>
              </div>
            </div>
          </th>
        </tr>
      </tfoot>
    @endif
  </table>

  <div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="itemsModalLabel"><span id="type"></span>Returned Items</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

          <div class="mb-3 card transaction-items">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">SKU</th>
                  <th scope="col">Item</th>
                  <th scope="col">Supplier</th>
                  <th scope="col">Quantity</th>
                  <th scope="col">Amount</th>
                  <th scope="col">Total Amount</th>
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

  <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="returnModalLabel">Return Transaction</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div>
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <div class="modal-body">
            <div class="mb-3">
              <label for="transactionCode" class="form-label">Search Transaction Code</label>
              <input type="text" class="form-control" id="transactionCode" name="transactionCode" autocomplete="off">
            </div>
  
            <table class="table">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Supplier</th>
                  <th>Quantity</th>
                  <th>Amount</th>
                  <th>Return Quantity</th>
                </tr>
              </thead>
              <tbody id="transactionItems">
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button class="btn btn-danger" id="sendReturnItem" disabled>Return</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="discardModal" tabindex="-1" aria-labelledby="discardModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="discardModalLabel">Discard Items</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <span>Are you sure you want to discard all the items in this returned transaction?</span>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-outline-danger" id="discardBtn">Yes</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(function() {
      $("#clear-search").click(function() {
        $('input[name="search"]').val("");
        $("#searchForm").submit();
      });

      $('.page-select').change(function() {
        search();
      });

      function search(p, d) {
        var page = p || $('.page-select').val();
        location.href = `/return-transactions?page=${page}`;
      }

      $('.view-item').click(function() {
        let transaction = $(this).data('json');
        let transactionItems = transaction.transaction_items;
        let html = '';

        if (transactionItems.length > 0) {
          transactionItems.map(transactionItem => {
            html += `
                <tr>
                  <td>${transactionItem.item.sku}</td>
                  <td>${transactionItem.item.name}</td>
                  <td>${transactionItem.supplier.name}</td>
                  <td>${transactionItem.return_quantity}</td>
                  <td>${transactionItem.amount}</td>
                  <td>${transactionItem.return_total_amount}</td>
                </tr>
              `;
          });
          $("#items").html(html);
        } else {
          html += '<tr><th>No items found.</th></tr>'
          $("#items").html(html);
        }
      });

      let selectedTrans = null;
      let itemsToReturn = [];
      const transactions = JSON.parse(@json($transactions));
      let transactionOptions = transactions.map(transaction => {return {...transaction, label: transaction.transaction_code}});
      $("#transactionCode").autocomplete({
        minLength: 0,
        source: transactionOptions,
        focus: function( event, ui ) {
          $("#transactionCode").val( ui.item.label );
          selectedTrans = ui.item;
          return false;
        },
        select: function( event, ui ) {
          $("#transactionCode").val( ui.item.label );
          updateTransItems();
          return false;
        }
      })
      .autocomplete("instance")._renderItem = function( ul, item ) {
        return $( "<li>" )
          .append(`<div>${item.label}</div>`)
          .appendTo( ul );
      };

      function updateTransItems() {
        itemsToReturn = [];
        if (selectedTrans) {
          let itemHtml = '';
          let transItems = selectedTrans.items;
          transItems.map(transItem => {
            itemHtml += `
              <tr>
                <td>${transItem.item.name}</td>
                <td>${transItem.supplier.name}</td>
                <td>${transItem.quantity}</td>
                <td>${transItem.amount}</td>
                <td>
                  <input 
                    id="item-${transItem.id}" 
                    class="form-control qty" 
                    type="number" 
                    min="0" 
                    max="${transItem.quantity}"
                    value="0" 
                    autocomplete="off"/>  
                </td>
              </tr>
            `;
          });
          $('#transactionItems').html(itemHtml);

          $('.qty').change(function() {
            if ($(this).val() > parseFloat($(this).attr('max'))) {
              $(this).val($(this).attr('max'));
              alert("No enough stocks.");
            }
            getItemsQty();
          });
        } else {
          let itemHtml = '<tr><th>No Items Found.</th></tr>'
          $('#transactionItems').html(itemHtml);
        }
      }

      function getItemsQty() {
        let transItems = selectedTrans.items;
        transItems.map(item => {
          let iwqId = itemsToReturn.findIndex(iwq => iwq.transItemId == item.id);
          let qty = parseFloat($(`#item-${item.id}`).val());
          let total = parseFloat(qty) * parseFloat(item.amount);
          if (qty > 0) { 
            if (iwqId >= 0) {
              itemsToReturn[iwqId].returnQty = qty;
              itemsToReturn[iwqId].total_amount = total;
            } else {
              itemsToReturn.push({
                transItemId: item.id, 
                amount: item.amount,
                stock: item.item.stock,
                returnQty: qty,
                total_amount: total
              });
            }
          } else {
            if (iwqId >= 0) {
              itemsToReturn.splice(iwqId, 1);
            }
          }
        });

        if (itemsToReturn.length > 0) {
          $('#sendReturnItem').removeAttr('disabled');
        } else {
          $('#sendReturnItem').attr('disabled', true);
        }
      }

      $('#sendReturnItem').on('click', () => {
        let form = document.createElement("form");

        form.method = "POST";
        form.action = `/return-transaction`;
        form.style.cssText = "display:none;"

        let tokenForm = document.createElement("input");
        tokenForm.value = "{{ csrf_token() }}";
        tokenForm.name = "_token";
        form.appendChild(tokenForm);

        let transId = document.createElement("input");
        transId.value = selectedTrans.id;
        transId.name = "transId";
        form.appendChild(transId);

        let itemsForm = document.createElement("input");
        itemsForm.value = JSON.stringify(itemsToReturn);
        itemsForm.name = "items";
        form.appendChild(itemsForm);

        let totalReturnQty = 0;
        let totalReturnAmount = 0;
        itemsToReturn.map(iwq => {
          totalReturnAmount = totalReturnAmount + parseFloat(iwq.total_amount);
          totalReturnQty = totalReturnQty + parseFloat(iwq.returnQty);
        });

        let totalQty = document.createElement("input");
        totalQty.value = JSON.stringify(totalReturnQty);
        totalQty.name = "totalReturnQty";
        form.appendChild(totalQty);

        let totalPrc = document.createElement("input");
        totalPrc.value = JSON.stringify(totalReturnAmount);
        totalPrc.name = "totalReturnAmount";
        form.appendChild(totalPrc);

        document.body.appendChild(form);
        form.submit();
      });

      $('.pickup').click(function() {
        let returnTransaction = $(this).data('json');
        updateStatus(returnTransaction, 'pickup');
      });

      let discardTransaction = null;
      $('.discard').click(function() {
        discardTransaction = $(this).data('json');
      });

      $('#discardBtn').on('click', function() {
        if (discardTransaction) {
          updateStatus(discardTransaction, 'discard');
        }
      });

      function updateStatus(returnTransaction, status) {
        let form = document.createElement("form");
        form.method = "POST";
        form.action = `/return-transaction/${returnTransaction.id}/status`;
        form.style.cssText = "display:none;"

        let tokenForm = document.createElement("input");
        tokenForm.value = "{{ csrf_token() }}";
        tokenForm.name = "_token";
        form.appendChild(tokenForm);

        let statusInput = document.createElement("input");
        statusInput.value = status;
        statusInput.name = "status";
        form.appendChild(statusInput);

        let itemInput = document.createElement("input");
        itemInput.value = JSON.stringify(returnTransaction.transaction_items);
        itemInput.name = "transItems";
        form.appendChild(itemInput);

        document.body.appendChild(form);
        form.submit();
      }
    });
  </script>
@endsection