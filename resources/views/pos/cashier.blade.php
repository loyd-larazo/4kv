@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Cashier</h1>
    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#closeCashierModal">Close Cashier</button>
  </nav>

  @if(\Session::get('error'))
    <div class="alert alert-danger text-center" role="alert">
      {{ \Session::get('error') }}
    </div>
  @endif

  @if($success)
    <div class="alert alert-success text-center mt-2" role="alert">
      {{ "Sales has been saved! Printing receipt." }}
    </div>
  @endif

  <div class="row">
    <div class="mt-2 p-0 col-sm-12 col-lg-5">
      <div class="card bg-light">
        <div class="alert alert-danger text-center d-none" id="outOfStock" role="alert">
          Out of stock!
        </div>

        <div class="row m-0 mt-2 p-1">
          <div class="row m-0 col-10 p-0">
            <div class="col-12 p-0">
              <input type="text" class="form-control" id="sku" placeholder="Search by Name or SKU" autocomplete="off">  
            </div>
            <div class="col-12 p-0 mt-2">
              <select id="category" class="form-control">
                <option>Search by Category</option>
                @foreach(json_decode($categories) as $category)
                  <option value="{{$category->id}}">{{ $category->name }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-2 p-0">
            <button class="btn btn-outline-primary col-12 h-100" id="searchItem">
              <i class="fa-solid fa-magnifying-glass"></i>
            </button>
          </div>
          <hr class="mt-4"/>
        </div>

        <div class="row m-0 p-0 search-items-container">
          <table class="table">
            <thead class="thead-light">
              <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Stock</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="itemResults">
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="col-sm-12 col-lg-7 mt-4 mt-sm-4 mt-lg-0">
      <h3>Cart Items</h3>
      <table class="table">
        <thead>
          <tr>
            <th scope="col">Item</th>
            <th scope="col">Quantity</th>
            <th scope="col">Price</th>
            <th scope="col">Total</th>
            <th scope="col"></th>
          </tr>
        </thead>
        <tbody id="items">
        </tbody>
      </table>
    </div>
  </div>

  <div>
    <div>
      <hr >
    </div>

    <div class="row m-0 p-0">
      <h2>Total Quantity: <span class="total-quantity"></span></h2>
      <h2>Total Price: <span class="total-price"></span></h2>
      <div>
        <div class="row">
          <h2 class="col-auto">Paid Amount:</h2>
          <div class="col-auto">
            <input type="number" id="amount" class="form-control h2" autocomplete="off"/>
          </div>
        </div>
      </div>
      <h2>Change: <span id="change"></span></h2>
    </div>

    <div class="row m-0 p-2">
      <button class="btn btn-outline-success" id="submitCheckout" disabled="disabled">
        <i class="fa-regular fa-credit-card me-1"></i> Checkout
      </button>
    </div>
  </div>

  <div class="modal fade" id="closeCashierModal" tabindex="-1" aria-labelledby="closeCashierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="closeCashierModalLabel">Close Cashier</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="/cashier/close" method="POST">
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <div class="modal-body">
            <div class="mb-3">
              <label for="closingAmount" class="form-label">Opening Amount: P{{ number_format($dailySale->opening_amount) }}</label>
            </div>
            <div class="mb-3">
              <label for="closingAmount" class="form-label">Closing Amount </label>
              <input type="number" class="form-control" id="closingAmount" name="closingAmount" autocomplete="off">
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-success" id="closeCashier" disabled>Close Cashier</button>
          </div>
          </form>
      </div>
    </div>
  </div>

  <script>
    $(function() {
      var saleId = {{ $success ?? "null" }};
      var productItems = JSON.parse(@json($items));
      var cart = [];
      var searchItems = [];
      var totalPrice = 0;
      var totalQuantity = 0;

      $('#sku').focus();
      $('#quantity').val(1);

      if (saleId) {
        setTimeout(() => {
          window.open(`/sale/${saleId}`);
          $('.alert-success').hide();
        }, 1000);
      }

      var itemSearch = productItems.map(item => {return {...item, label: item.name}});
      $("#sku").autocomplete({
        minLength: 0,
        source: itemSearch,
        focus: function( event, ui ) {
          $("#sku").val( ui.item.label );
          return false;
        },
        select: function( event, ui ) {
          $("#sku").val( ui.item.label );
          searchItems = [ui.item];
          updateSearchItems();
          return false;
        }
      })
      .autocomplete("instance")._renderItem = function( ul, item ) {
        return $( "<li>" )
          .append(`<div>${item.label}<br>SKU: ${item.sku}</div>`)
          .appendTo( ul );
      };

      $("#category").change(function() {
        if ($(this).val()) {
          var categoryId = $(this).val();
          searchItems = productItems.filter(item => item.category_id == categoryId);
          updateSearchItems();
        }
      });

      $('#searchItem').click(function() {
        var sku = $("#sku").val();
        var selectedItem = productItems.find(pItem => pItem.sku == sku);

        searchItems = selectedItem ? [selectedItem] : [];
        updateSearchItems();
      });

      $('#amount').keyup(function() {
        if (totalQuantity && $(this).val() > 0 && totalPrice <= $(this).val()) {
          $('#submitCheckout').removeAttr('disabled');
          $("#change").html($(this).val() - totalPrice);
        } else {
          $('#submitCheckout').attr('disabled', 'disabled');
          $("#change").html("0.00");
        }
      });

      $("#closingAmount").keyup(function() {
        if ($(this).val() > 0) {
          $("#closeCashier").removeAttr('disabled');
        } else {
          $("#closeCashier").attr('disabled', 'disabled');
        }
      });

      $('#submitCheckout').click(function() {
        var form = document.createElement("form");

        form.method = "POST";
        form.action = "/sales";
        form.style.cssText = "display:none;"

        var tokenForm = document.createElement("input");
        tokenForm.value = "{{ csrf_token() }}";
        tokenForm.name = "_token";
        form.appendChild(tokenForm);

        var itemsForm = document.createElement("input");
        itemsForm.value = JSON.stringify(cart);
        itemsForm.name = "items";
        form.appendChild(itemsForm);

        var totalQty = document.createElement("input");
        totalQty.value = JSON.stringify(totalQuantity);
        totalQty.name = "totalQuantity";
        form.appendChild(totalQty);

        var totalPrc = document.createElement("input");
        totalPrc.value = JSON.stringify(totalPrice);
        totalPrc.name = "totalPrice";
        form.appendChild(totalPrc);

        var amountPaid = document.createElement("input");
        amountPaid.value = JSON.stringify(parseFloat($('#amount').val()));
        amountPaid.name = "amount";
        form.appendChild(amountPaid);

        document.body.appendChild(form);
        form.submit();
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

      function updateSearchItems() {
        if (searchItems.length) {
          let itemHtml = '';
          searchItems.map(item => {
            itemHtml += `
            <tr>
              <td class="align-middle">
                ${item.name}<br />
                SKU: ${item.sku}<br />
                Sold by: ${item.sold_by_weight ? 'Weight' : (item.sold_by_length ? 'Length' : 'Stock')}
              </td>
              <td class="align-middle">${item.price}</td>
              <td class="align-middle">${item.stock}</td>
              <td class="align-middle">
                <button class="btn btn-sm btn-success add-to-cart" data-id="${item.id}">
                  <i class="fa-solid fa-plus"></i>  
                </button>  
              </td>
            </tr>`;
          });
          $('#itemResults').html(itemHtml);
          $('#sku').val("");
          $('#category').val($("#category option:first").val());

          
          $('.add-to-cart').click(function() {
            var itemId = $(this).data('id');
            var item = productItems.find(pitem => pitem.id == itemId);
            var cartIndex = cart.findIndex(c => c.id == itemId);
            if (cartIndex >= 0) {
              item = cart[cartIndex];
              item.quantity = (item.quantity + 1) > item.stock ? item.stock : (item.quantity + 1);
              cart[cartIndex] = item;
            } else {
              item.quantity = 1;
              cart.push(item);
            }
            populateCart();
          })
        } else {
          $('#itemResults').html(`<tr>
            <td class="text-center" colspan="4">No Item found</td>
          </tr>`);
        }
      }

      function populateCart() {
        var html = "";
        totalPrice = 0;
        totalQuantity = 0;
        cart.map(item => {
          var itemTotal = item.price * item.quantity;
          totalPrice += itemTotal;
          totalQuantity += item.quantity;
          html += `
            <tr>
              <td class="align-middle">
                ${item.name}<br />
                SKU: ${item.sku}<br />
                Sold by: ${item.sold_by_weight ? 'Weight' : (item.sold_by_length ? 'Length' : 'Stock')}
              </td>
              <td class="align-middle">
                <input type="number" class="form-control form-control-sm car-qty" value="${item.quantity}" data-id="${item.id}" max="${item.stock}" step="${(item.sold_by_weight || item.sold_by_length) ? '0.1' : '1'}" autocomplete="off"/> 
              </td>
              <td class="align-middle">P${formatMoney(item.price, 2, '.', ',')}</td>
              <td class="fw-bold align-middle">P${formatMoney(itemTotal, 2, '.', ',')}</td>
              <td class="align-middle">
                <button class="btn btn-sm btn-danger delete-cart" data-id="${item.id}">
                  <i class="fa-solid fa-trash-can"></i>
                </button>  
              </td>
            </tr>
          `;
        });

        $("#items").html(html);
        $(".total-quantity").html(totalQuantity);
        $(".total-price").html(`P${formatMoney(totalPrice, 2, '.', ',')}`);
        if (totalQuantity && $("#amount").val() > 0 && totalPrice <= $("#amount").val()) {
          $('#submitCheckout').removeAttr('disabled');
        } else {
          $('#submitCheckout').attr('disabled', 'disabled');
        }

        $('.car-qty').change(function() {
          var itemId = $(this).data('id');
          var cartItemIndex = cart.findIndex(c => c.id == itemId);

          var max = (cart[cartItemIndex].sold_by_weight || cart[cartItemIndex].sold_by_length) ? parseFloat($(this).attr('max')) : parseInt($(this).attr('max'));
          var qty = (cart[cartItemIndex].sold_by_weight || cart[cartItemIndex].sold_by_length) ? parseFloat($(this).val()) : parseInt($(this).val());

          if (max < qty) {
            qty = max;
          }

          cart[cartItemIndex].quantity = qty;
          populateCart();
        });

        $('.delete-cart').click(function() {
          var itemId = $(this).data('id');
          var cartItemIndex = cart.findIndex(c => c.id == itemId);
          cart.splice(cartItemIndex, 1);
          populateCart();
        });
      }
    });
  </script>
@endsection