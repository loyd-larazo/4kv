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
  <div class="alert alert-success text-center" id="itemSuccess" role="alert">Item has been saved!</div>

  <div class="">
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

    <div class="">
      <div class="mb-3">
        <label class="form-label">Stock Man</label>
        <input type="text" class="form-control" placeholder="Stock Man" id="stockman" value="{{$user->firstname." ".$user->lastname}}" @disabled(true) autocomplete="off"/>
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
                  <option value="">Search by Category</option>
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
            <div class="col-12 text-center">
              Can't find item? Click <a href="#" id="addNewItem" data-bs-toggle="modal" data-bs-target="#itemsModal">here</a> to add new item.
            </div>
            <div id="addSupplierHolder" class="col-12 text-center">
              Can't find supplier? Click <a href="#" id="addNewSupplier" data-bs-toggle="modal" data-bs-target="#supplierModal">here</a> to add new supplier.
            </div>
            <hr class="mt-4"/>
          </div>
  
          <div class="row m-0 p-0 search-items-container">
            <table class="table table-header-fixed">
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
              <th scope="col"></th>
            </tr>
          </thead>
          <tbody id="items">
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal-footer">
      <h4 class="me-4">Total Cost: P<span id="totalCost">0</span></h4>
      <button type="submit" class="btn btn-outline-success" id="submitTransaction" disabled>Save</button>
    </div>
  </div>

  <div class="modal fade" id="itemsModal" tabindex="-1" aria-labelledby="itemsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="/" method="POST" id="addItemForm">
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <div class="modal-header">
            <h5 class="modal-title" id="itemsModalLabel">Add Item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="modalError" class="alert alert-danger text-center d-none" role="alert"></div>

            <div class="mb-3">
              <label class="form-label">Product Name</label>
              <input type="text" class="form-control" name="name" required autocomplete="off">
            </div>

            <div class="mb-3">
              <label class="form-label">Cost</label>
              <input type="number" class="form-control" name="cost" required autocomplete="off" min="0">
            </div>

						<div class="mb-3">
              <label class="form-label">Price</label>
              <input type="number" class="form-control" name="price" required autocomplete="off" min="0">
            </div>

						<div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" name="description" required></textarea>
            </div>

						@if(isset($categories))
							<div class="mb-3">
								<label class="form-label">Category</label>
								<select class="form-select" name="category" required>
									<option value="">Select Category</option>
									@foreach(json_decode($categories) as $category)
										<option value="{{ $category->id }}">{{ $category->name }}</option>
									@endforeach			
								</select>
							</div>
						@endif

						<div class="mb-3">
              <label class="form-label">Sold by</label>
              <select class="form-select" name="sold_by" required>
                <option value="">Select Option</option>
                <option value="stock">Stock</option>
                <option value="weight">Weight</option>
                <option value="length">Length</option>
              </select>
            </div>

						<div class="mb-3">
              <label class="form-label">Status</label>
              <select class="form-select" name="status" required>
                <option value="">Select Option</option>
                <option value="1">Active</option>
                <option value="0">Disabled</option>
              </select>
            </div>

          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="/supplier" method="POST">
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <input type="hidden" name="id" />
          <div class="modal-header">
            <h5 class="modal-title" id="supplierModalLabel"><span id="type"></span> Supplier</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" name="name" required autocomplete="off">
            </div>

            <div class="mb-3">
              <label class="form-label">Contact Person</label>
              <input type="text" class="form-control" name="contact_person" required autocomplete="off">
            </div>

            <div class="mb-3">
              <label class="form-label">Contact Number</label>
              <input type="number" class="form-control" name="contact_number" required autocomplete="off">
            </div>

            <div class="mb-3">
              <label class="form-label">Address</label>
              <textarea class="form-control" name="address" required></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Status</label>
              <select class="form-select" name="status" required>
                <option value="">Select Option</option>
                <option value="1">Active</option>
                <option value="0">Disabled</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    $(function() {
      $('#itemSuccess').hide();
      var productItems = JSON.parse(@json($items));
      var suppliers = JSON.parse(@json($suppliers));
      var addedItems = [];
      var searchItems = [];

      var supplierOptions = "";
      suppliers.map(supplier => {
        supplierOptions += `<option value="${supplier.id}">${supplier.name}</option>`;
      });

      var itemSearch = productItems.map(item => {return {...item, label: item.name}});
      initializeAutocomplete();
      function initializeAutocomplete() {
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
      }

      $("#category").change(function() {
        if ($(this).val()) {
          var categoryId = $(this).val();
          searchItems = productItems.filter(item => item.category_id == categoryId);
          updateSearchItems();
        } else {
          $('#itemResults').html('');
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
          $('#category').val($("#category option:selected").val());

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

            var itemIndex = addedItems.findIndex(ai => (ai.id == item.id && ai.supplier_id == searchItem.supplier_id));
            if (itemIndex >= 0) {
              var addedItem = addedItems[itemIndex];
              addedItem.quantity = parseFloat(addedItem.quantity) + 1;
              addedItems[itemIndex] = addedItem;
            } else {
              searchItem.quantity = 1;
              addedItems.push(JSON.parse(JSON.stringify(searchItem)));
            }

            populateItems();
          })
        } else {
          $('#itemResults').html(`<tr>
            <td class="text-center" colspan="5">No Item found</td>
          </tr>`);
        }
      }

      function populateItems() {
        var html = "";
        var overallCost = 0;
        
        addedItems.map((aItem, index) => {
          var selectedSupp = suppliers.find(supp => supp.id == aItem.supplier_id);
          var totalCost = aItem.cost && aItem.quantity ? aItem.cost * aItem.quantity : 0;
          overallCost += totalCost;

          html += `
            <tr data-index="${index}">
              <td>${aItem.sku}</td>
              <td>${aItem.name}</td>
              <td>${selectedSupp.name}</td>
              <td><input class="form-control added-item-qty" type="number" value="${aItem.quantity}" data-id="${aItem.id}" step="${(aItem.sold_by_weight || aItem.sold_by_length) ? '0.1' : '1'}" autocomplete="off"></td>
              <td><input class="form-control added-item-cost" type="number" placeholder="Cost" value="${aItem.cost}" data-id="${aItem.id}" autocomplete="off"></td>
              <td>${formatMoney(totalCost, 2, '.', ',')}</td>
              <td>
                <button data-id="${aItem.id}" class="btn btn-sm btn-danger delete-item">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              </td>
            </tr>
          `;
        });
        $("#items").html(html);
        $("#totalCost").html(formatMoney(overallCost, 2, '.', ','));

        $('.delete-item').click(function() {
          var id = $(this).data('id');
          var addItemIndex = addedItems.findIndex(ai => ai.id == id);
          if (addItemIndex >= 0) {
            addedItems.splice(addItemIndex, 1);
            populateItems();
          }
        });

        $('.added-item-qty').on('change', function() {
          var itemId = $(this).data('id');
          var addedItemIndex = $(this).closest('tr').data('index');
          // var addedItemIndex = addedItems.findIndex(c => c.id == itemId);
          var qty = (addedItems[addedItemIndex].sold_by_weight || addedItems[addedItemIndex].sold_by_length) ? parseFloat($(this).val()) : parseInt($(this).val());

          addedItems[addedItemIndex].quantity = qty;
          populateItems();
        });

        $('.added-item-cost').change(function() {
          var itemId = $(this).data('id');
          var addedItemIndex = $(this).closest('tr').data('index');
          // var addedItemIndex = addedItems.findIndex(c => c.id == itemId);

          addedItems[addedItemIndex].cost = $(this).val();
          populateItems();
        });

        if (addedItems.length) {
          $('#submitTransaction').removeAttr("disabled");
        } else {
          $('#submitTransaction').attr("disabled", "disabled");
        }
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

      $('#addNewItem').click(function() {
        $('#modalError').html("").addClass('d-none');

        $('input[name="name"]').val("");
        $('input[name="cost"]').val("");
        $('input[name="price"]').val("");
        $('textarea[name="description"]').val("");
        $('select[name="sold_by"]').val("");
        $('select[name="category"]').val("");
        $('input[name="stock"]').val("");
        $('select[name="status"]').val("");
      });

      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
      });

      $('#addItemForm').submit(function(e) {
        e.preventDefault();
        $('#modalError').html("").addClass('d-none');

        var cost = $('input[name="cost"]').val();
        var price = $('input[name="price"]').val();
        if (cost > price) {
          $('#modalError').html("The cost shouldn't higher than the price.").removeClass('d-none');
          document.getElementById("itemsModal").scrollTop = 0;
        } else {
          var name = $('input[name="name"]').val();
          var category = $('select[name="category"]').val();

          $.ajax({
            type: 'GET',
            dataType: 'json',
            url: `/validate/item/category/${category}?name=${name}`,
            success: (data) => {
              if (data.data) {
                $('#modalError').html("Item with the same name and category is already exists.").removeClass('d-none');
                document.getElementById("itemsModal").scrollTop = 0;
              } else {
                addNewItem();
              }
            }
          });
        }
      });

      function addNewItem() {
        $.ajax({
          type: 'POST',
          dataType: 'json',
          url: `/item`,
          data: {
            name: $('input[name="name"]').val(),
            cost: $('input[name="cost"]').val(),
            price: $('input[name="price"]').val(),
            description: $('textarea[name="description"]').val(),
            sold_by: $('select[name="sold_by"]').val(),
            category: $('select[name="category"]').val(),
            stock: $('input[name="stock"]').val(),
            status: $('select[name="status"]').val(),
            isAjax: true,
          },
          success: (data) => {
            productItems = data.data;
            itemSearch = productItems.map(item => {return {...item, label: item.name}});
            initializeAutocomplete();
            $('#itemsModal').modal('hide');
            $('#itemSuccess').show();
            // setTimeout(() => { $('#itemSuccess').hide(); } , 2000);
          }
        });
      }

      $('#addNewSupplier').click(function() {
        $('#type').html("Add");
      });

      $('input[name="cost"]').change(function() {
        setToMin($(this));
      });

      $('input[name="price"]').change(function() {
        setToMin($(this));
      });

      function setToMin(selector) {
        let val = selector.val();
        let minVal = selector.attr('min');

        if (val < minVal) selector.val(minVal);
      };
    });
  </script>
@endsection