@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Sales</h1>
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
    <div class="col-7">
      <table class="table">
        <thead>
          <tr>
            <th scope="col">SKU</th>
            <th scope="col">Item</th>
            <th scope="col">Quantity</th>
            <th scope="col">Price</th>
            <th scope="col">Total</th>
          </tr>
        </thead>
        <tbody id="items">
        </tbody>
      </table>
    </div>
    <div class="col-5 mt-2 p-0">
      <div class="card">
        <div class="alert alert-danger text-center d-none" id="outOfStock" role="alert">
          Out of stock!
        </div>

        <div class="row m-0 mt-2 p-1">
          <div class="col-8 p-0">
            <input type="text" class="form-control" id="sku" placeholder="Search/Scan SKU">  
          </div>
          <div class="col-3 p-0">
            <input type="number" class="form-control" id="quantity" placeholder="Quantity">
          </div>
          <div class="col-1 p-0 text-center">
            <button class="btn btn-outline-primary" id="addItem">
              <i class="fa-solid fa-cart-plus"></i>
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
          <div class="col-12 fw-bold mt-2">
            Price: <span id="searchedPrice"></span>
          </div>
          <div class="col-12 fw-bold mt-2">
            Stock: <span id="searchedStock"></span>
          </div>
        </div>
        
        <div>
          <hr >
        </div>

        <div class="row m-0 p-0">
          <h2>Total Quantity: <span class="total-quantity"></span></h2>
          <h2>Total Price: <span class="total-price"></span></h2>
        </div>

        <div class="row m-0 p-2">
          <button class="btn btn-outline-success" id="submitCheckout">
            <i class="fa-regular fa-credit-card me-1"></i> Checkout
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(function() {
      var saleId = {{ $success ?? "null" }};
      var productItems = JSON.parse(@json($items));
      var cart = [];
      var totalQuantity = 0;
      var totalPrice = 0;

      $('#sku').focus();
      $('#quantity').val(1);

      if (saleId) {
        setTimeout(() => {
          window.open(`/sale/${saleId}`);
          $('.alert-success').hide();
        }, 1000);
      }

      $('#sku').keyup(function(e) {
        var sku = $(this).val();
        var selectedItem = productItems.find(pItem => pItem.sku == sku);

        if (selectedItem) {
          $('#searchedSku').html(selectedItem.sku);
          $('#searchedName').html(selectedItem.name);
          $('#searchedPrice').html(`P${selectedItem.price}`);
          $('#searchedStock').html(`${selectedItem.stock}`);
        } else {
          $('#searchedSku').html("Not found");
          $('#searchedName').html("Not found");
          $('#searchedPrice').html("Not found");
          $('#searchedStock').html("Not found");
        }

        var code = e.key;
        if (code === "Enter") {
          $('#addItem').click();
        }
      });

      $('#quantity').keyup(function(e) {
        var code = e.key;
        if (code === "Enter") {
          $('#addItem').click();
        }
      });

      $('#addItem').click(function() {
        var sku = $("#sku").val();
        var quantity = $("#quantity").val();
        $('#outOfStock').addClass('d-none');

        var selectedItem = productItems.find(pItem => pItem.sku == sku);

        if (selectedItem && quantity) {
          if (selectedItem.stock < quantity) {
            $('#outOfStock').removeClass('d-none');
            return;
          }

          totalQuantity = 0;
          totalPrice = 0;

          var itemIndex = cart.findIndex(ai => ai.sku == sku);
          if (itemIndex >= 0) {
            var addedItem = cart[itemIndex];
            if ((parseInt(addedItem.quantity) + parseInt(quantity)) > selectedItem.stock) {
              $('#outOfStock').removeClass('d-none');
              return;
            }
            addedItem.quantity = parseInt(addedItem.quantity) + parseInt(quantity);
          } else {
            selectedItem.quantity = parseInt(quantity);
            cart.push(selectedItem);
          }

          var html = "";
          cart.map(item => {
            var itemTotal = item.price * item.quantity;
            totalPrice += itemTotal;
            totalQuantity += item.quantity;
            html += `
              <tr>
                <td>${item.sku}</td>
                <td>${item.name}</td>
                <td>${item.quantity}</td>
                <td>P${formatMoney(item.price, 2, '.', ',')}</td>
                <td class="fw-bold">P${formatMoney(itemTotal, 2, '.', ',')}</td>
              </tr>
            `;
          });
          $("#items").html(html);
          $("#sku").val("");
          $("#quantity").val(1);
          $(".total-quantity").html(totalQuantity);
          $(".total-price").html(`P${formatMoney(totalPrice, 2, '.', ',')}`);
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
    });
  </script>
@endsection