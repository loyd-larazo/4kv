@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Add Transactions</h1>
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

  <div class="">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="">
      <div class="mb-3">
        <label class="form-label">Stock Man</label>
        <input type="text" class="form-control" placeholder="Stock Man" id="stockman" value="{{$user->firstname." ".$user->lastname}}" @disabled(true)/>
      </div>

      <div class="mb-3">
        <label class="form-label">Remarks</label>
        <textarea class="form-control" name="remarks" required></textarea>
      </div>

      <div class="mt-2 p-0 col-12">
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
                  <th>Supplier</th>
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

      {{-- <div class="mb-3" id="scanItems">
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
      </div> --}}

      <div class="mb-3 mt-3 card transaction-items p-2">
        <h4>Transaction Items</h4>
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

    <div class="modal-footer">
      <h4 class="me-4">Total Cost: P<span id="totalCost">0</span></h4>
      <button type="submit" class="btn btn-outline-success" id="submitTransaction">Save</button>
    </div>
  </div>

  <script>
    $(function() {
      var productItems = JSON.parse(@json($items));
      var suppliers = JSON.parse(@json($suppliers));
      var addedItems = [];
      var searchItems = [];

      var supplierOptions = "";
      suppliers.map(supplier => {
        supplierOptions += `<option value="${supplier.id}">${supplier.name}</option>`;
      });

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
              <td class="align-middle">
                <select class="form-control supplier-select" data-id="${item.id}">
                  <option>Select Supplier</option>
                  ${supplierOptions}
                </select>  
              </td>
              <td class="align-middle">${item.price}</td>
              <td class="align-middle">${item.stock}</td>
              <td class="align-middle">
                <button class="btn btn-sm btn-success add-to-transaction" data-id="${item.id}">
                  <i class="fa-solid fa-plus"></i>  
                </button>  
              </td>
            </tr>`;
          });
          $('#itemResults').html(itemHtml);
          $('#sku').val("");
          $('#category').val($("#category option:first").val());

          $('.supplier-select').change(function() {
            var searchItemId = $(this).data('id');
            var supplierId = $(this).val();

            var searchItemIndex = searchItems.findIndex(si => si.id == searchItemId);
            searchItems[searchItemIndex].supplier_id = supplierId;
          });

          
          $('.add-to-transaction').click(function() {
            var itemId = $(this).data('id');
            var item = productItems.find(pitem => pitem.id == itemId);
            var searchItemIndex = searchItems.findIndex(si => si.id == itemId);
            var searchItem = searchItems[searchItemIndex];
            if (!searchItem.supplier_id) {
              return alert("Please select supplier.");
            }

            var itemIndex = addedItems.findIndex(ai => (ai.id == item.id && ai.supplier == searchItem.supplier_id));
            if (itemIndex >= 0) {
              var addedItem = addedItems[itemIndex];
              addedItem.quantity = parseFloat(addedItem.quantity) + 1;
              addedItems[itemIndex] = addedItem;
            } else {
              searchItem.quantity = 1;
              addedItems.push(searchItem);
            }

            populateItems();
          })
        } else {
          $('#itemResults').html(`<tr>
            <td class="text-center" colspan="5">No Item found</td>
          </tr>`);
        }
      }

      function populateItems(item) {
        var html = "";
        var overallCost = 0;
        addedItems.map(aItem => {
          var selectedSupp = suppliers.find(supp => supp.id == aItem.supplier_id);
          var totalCost = aItem.cost && aItem.quantity ? aItem.cost * aItem.quantity : 0;
          overallCost += totalCost;

          html += `
            <tr>
              <td>${aItem.sku}</td>
              <td>${aItem.name}</td>
              <td>${selectedSupp.name}</td>
              <td><input class="form-control added-item-qty" type="number" value="${aItem.quantity}" data-id="${aItem.id}" step="${(aItem.sold_by_weight || aItem.sold_by_length) ? '0.1' : '1'}"></td>
              <td><input class="form-control added-item-cost" type="number" placeholder="Cost" value="${aItem.cost}" data-id="${aItem.id}"></td>
              <td>${formatMoney(totalCost, 2, '.', ',')}</td>
            </tr>
          `;
        });
        $("#items").html(html);
        $("#totalCost").html(formatMoney(overallCost, 2, '.', ','));


        $('.added-item-qty').on('change', function() {
          var itemId = $(this).data('id');
          var addedItemIndex = addedItems.findIndex(c => c.id == itemId);
          var qty = (addedItems[addedItemIndex].sold_by_weight || addedItems[addedItemIndex].sold_by_length) ? parseFloat($(this).val()) : parseInt($(this).val());

          addedItems[addedItemIndex].quantity = qty;
          populateItems();
        });

        $('.added-item-cost').change(function() {
          var itemId = $(this).data('id');
          var addedItemIndex = addedItems.findIndex(c => c.id == itemId);

          addedItems[addedItemIndex].cost = $(this).val();
          populateItems();
        });
      }

      $('#submitTransaction').click(function() {
        var remarks = $('textarea[name="remarks"]').val();

        var form = document.createElement("form");

        form.method = "POST";
        form.action = "/transaction";
        form.style.cssText = "display:none;"

        var tokenForm = document.createElement("input");
        tokenForm.value = "{{ csrf_token() }}";
        tokenForm.name = "_token";
        form.appendChild(tokenForm);  

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