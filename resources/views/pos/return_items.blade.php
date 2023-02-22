@extends('app')

@section('content')
<nav class="navbar navbar-light bg-light">
  <h1>Returned Items</h1>
  @if(isset($dailySale) && ($dailySale && !$dailySale->closing_user_id))
    <div class="">
      <a href="/return-items/damage-type" class="btn btn-sm btn-success">
        <i class="fa-regular fa-rectangle-list me-2"></i> Damage Items
      </a>
      <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#returnItems">
        <i class="fa-solid fa-right-left me-2"></i> Return Item
      </button>
    </div>
  @else
    <label>Cashier is already closed!</label>
  @endif
</nav>

@if(\Session::get('error'))
  <div class="alert alert-danger text-center" role="alert">
    {{ \Session::get('error') }}
  </div>
@endif

@if(\Session::get('success'))
  <div class="alert alert-success text-center mt-2" role="alert">
    {{ "Items has been returned!" }}
  </div>
@endif

<table class="table">
  <thead>
    <tr>
      <th class="mobile-col-sm" scope="col">Reference</th>
      <th class="mobile-col-sm" scope="col">Cashier</th>
      <th class="mobile-col-md" scope="col">Total Quantity</th>
      <th scope="col">Total Amount</th>
      <th scope="col">Date</th>
      <th scope="col"></th>
    </tr>
  </thead>
  <tbody>
    @if(isset($returns) && count($returns))
      @foreach($returns as $return)
        <?php 
          $strDate = strtotime($return->created_at);
          $transDate = getDate($strDate);
        ?>
        <tr>
          <td class="">{{ $return->reference }}</td>
          <td class="mobile-col-sm">{{ $return->user->firstname . ' ' . $return->user->lastname }}</td>
          <td class="mobile-col-md">{{ $return->total_quantity }}</td>
          <td>P{{ number_format($return->total_amount) }}</td>
          <td>{{ $transDate['month']." ".$transDate['mday'].", ".$transDate['year'] }}</td>
          <td>
            <button 
              class="btn btn-sm btn-outline-primary view-sales" 
              data-bs-toggle="modal" 
              data-bs-target="#salesModal" 
              data-id="{{ $return->id }}" 
              data-json="{{ json_encode($return->items) }}"
              data-damage="{{ json_encode($return->damageItems) }}">
              <i class="fa-regular fa-rectangle-list"></i>
            </button>
          </td>
        </tr>
      @endforeach
    @else
      <tr>
        <th colspan="7" class="text-center">No return found.</th>
      </tr>
    @endif
  </tbody>
  @if(isset($returns) && count($returns))
    <tfoot>
      <tr>
        <th colspan="12" class="text-center">
          <div class="row g-3 align-items-center">
            <div class="col-auto">
              <label class="col-form-label">Select Page</label>
            </div>
            <div class="col-auto">
              <select class="form-select page-select">
                @for($i = 1; $i <= $returns->lastPage(); $i++)
                  <option value="{{ $i }}" {{ $returns->currentPage() == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
              </select>
            </div>
          </div>
        </th>
      </tr>
    </tfoot>
  @endif
</table>

<div class="modal fade" id="returnItems" tabindex="-1" aria-labelledby="returnItemsLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="returnItemsLabel">Return Items</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div>
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <div class="modal-body">
          <div class="mb-3">
            <label for="reference" class="form-label">Search Reference</label>
            <input type="text" class="form-control" id="reference" name="reference" autocomplete="off">
          </div>

          <table class="table">
            <thead>
              <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Amount</th>
                <th>Total Amount</th>
                <th>Type</th>
                <th>Return Quantity</th>
              </tr>
            </thead>
            <tbody id="salesItems">
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

<div class="modal fade" id="salesModal" tabindex="-1" aria-labelledby="salesModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="salesModalLabel">Returned Items</h5>
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
              <th>Type</th>
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
    var sales = JSON.parse(@json($sales));
    var selectedSales = null;
    var itemsWithQty = [];

    var selesSearch = sales.map(item => {return {...item, label: item.reference}});
    initializeAutocomplete();
    function initializeAutocomplete() {
      $("#reference").autocomplete({
        minLength: 0,
        source: selesSearch,
        focus: function( event, ui ) {
          $("#reference").val( ui.item.label );
          return false;
        },
        select: function( event, ui ) {
          $("#reference").val( ui.item.label );
          selectedSales = ui.item;

          updateSalesItems();
          return false;
        }
      })
      .autocomplete("instance")._renderItem = function( ul, item ) {
        return $( "<li>" )
          .append(`<div>${item.label}</div>`)
          .appendTo( ul );
      };
    }

    function updateSalesItems() {
      if (selectedSales) {
        var itemHtml = '';
        var items = selectedSales.items;
        items.map(item => {
          itemHtml += `
            <tr>
              <td>${item.item.name}</td>
              <td>${item.quantity}</td>
              <td>${item.amount}</td>
              <td>${item.total_amount}</td>
              <td>
                <select class="form-select" data-id="${item.id}" name="returnType" required>
                  <option value="wrong">Wrong Item</option>
                  <option value="damage">Damage Item</option>
                </select>
              </td>
              <td>
                <input id="item-${item.id}" class="form-control qty" type="number" min="0" max="${item.quantity}" value="0" autocomplete="off"/>  
              </td>
            </tr>
          `;
        });

        $('#salesItems').html(itemHtml);

        $('.qty').change(function() {
          if ($(this).val() > parseFloat($(this).attr('max'))) {
            $(this).val($(this).attr('max'));
          }
          getItemsQty();
        });

        $('select[name="returnType"]').change(function() {
          var returnType = $(this).val();
          var id = $(this).data('id');
          var selectedSaleItemId = selectedSales.items.findIndex(item => item.id == id);
          if (selectedSaleItemId >= 0) selectedSales.items[selectedSaleItemId]['returnType'] = returnType;
        });
      }
    }

    function getItemsQty() {
      var items = selectedSales.items;
      items.map(item => {
        var iwqId = itemsWithQty.findIndex(iwq => iwq.id == item.id);
        var qty = parseFloat($(`#item-${item.id}`).val());
        var returnType = item.returnType ? item.returnType : 'wrong';
        if (qty > 0) { 
          if (iwqId >= 0) {
            itemsWithQty[iwqId]['returnType'] = returnType;
            itemsWithQty[iwqId].quantity = qty;
            itemsWithQty[iwqId].total_amount = parseFloat(qty) * parseFloat(item.amount);
          } else {
            itemsWithQty.push({
              id: item.id, 
              quantity: qty,
              item_id: item.item_id,
              amount: item.amount,
              total_amount: parseFloat(qty) * parseFloat(item.amount),
              returnType
            });
          }
        } else {
          if (iwqId >= 0) {
            itemsWithQty.splice(iwqId, 1);
          }
        }
      });
      
      if (itemsWithQty.length) {
        $('#sendReturnItem').removeAttr('disabled');
      } else {
        $('#sendReturnItem').attr('disabled', 'disabled');
      }
    }

    $('#sendReturnItem').click(function() {
      var form = document.createElement("form");

      form.method = "POST";
      form.action = `/return-items/${selectedSales.id}`;
      form.style.cssText = "display:none;"

      var tokenForm = document.createElement("input");
      tokenForm.value = "{{ csrf_token() }}";
      tokenForm.name = "_token";
      form.appendChild(tokenForm);

      var itemsForm = document.createElement("input");
      itemsForm.value = JSON.stringify(itemsWithQty);
      itemsForm.name = "items";
      form.appendChild(itemsForm);

      var totalQuantity = 0;
      var totalAmount = 0;
      itemsWithQty.map(iwq => {
        totalAmount = totalAmount + parseFloat(iwq.total_amount);
        totalQuantity = totalQuantity + parseFloat(iwq.quantity);
      });

      var totalQty = document.createElement("input");
      totalQty.value = JSON.stringify(totalQuantity);
      totalQty.name = "totalQty";
      form.appendChild(totalQty);

      var totalPrc = document.createElement("input");
      totalPrc.value = JSON.stringify(totalAmount);
      totalPrc.name = "totalAmount";
      form.appendChild(totalPrc);

      document.body.appendChild(form);
      form.submit();
    });

    $('.view-sales').click(function() {
      $("#saleItems").html("");
      var items = $(this).data('json');
      var damageItems = $(this).data('damage');
      var html = '';

      items.map(item => {
        if (item.type == 'return') {
          html += `
              <tr>
                <td>${item.item.name}</td>
                <td>P${formatMoney(item.amount, 2, '.', ',')}</td>
                <td>${item.quantity}</td>
                <td>P${formatMoney(item.total_amount, 2, '.', ',')}</td>
                <td>Wrong Item</td>
              </tr>
            `;
        }
      });

      if (damageItems.length > 0) {
        damageItems.map(item => {
          html += `
              <tr>
                <td>${item.item.name}</td>
                <td>P${formatMoney(item.amount, 2, '.', ',')}</td>
                <td>${item.quantity}</td>
                <td>P${formatMoney(item.total_amount, 2, '.', ',')}</td>
                <td>Damage Item</td>
              </tr>
            `;
        });
      }
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