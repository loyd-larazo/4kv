@extends('app')

@section('content')
  <?php 
    $user = Session::get('user');
  ?>
  <nav class="navbar navbar-light bg-light">
    <h1>Items</h1>

    <form class="row g-3 align-items-center" action="/items" method="GET" id="searchForm">
      <div class="col-auto row p-0 m-0 mt-3">
        <label class="form-label col-auto pt-2">Status: </label>
        <select id="selectStatus" name="status" class="form-control col">
          <option {{!isset($status) || (isset($status) && $status == 1) ? 'selected' : ''}} value="1">Active</option>
          <option {{(isset($status) && $status == 0) ? 'selected' : ''}} value="0">Disabled</option>
          <option {{($isZeroStock == 1) ? 'selected' : ''}} value="-1">0 Stock</option>
        </select>
      </div>
      <div class="col-auto">
        <div class="form-control clear-input">
          <input type="text" class="form-control" placeholder="Search SKU or Item" name="search" value="{{$search}}" autocomplete="off">
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

    @if(in_array($user->type, ["admin", "stock man"]))
      <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#itemsModal" id="addItem">
        Add Item
      </button>
    @endif
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
        <th class="mobile-col-sm" scope="col">SKU</th>
        <th scope="col">Item</th>
        <th scope="col">Cost</th>
        <th scope="col">Price</th>
        <th class="mobile-col-lg" scope="col">Description</th>
        <th class="mobile-col-md" scope="col">Category</th>
        <th class="mobile-col-sm" scope="col">Sold By</th>
        <th scope="col">Stock</th>
        {{-- <th class="mobile-col-md" scope="col">Status</th> --}}
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
      @if($items && count($items))
        @foreach($items as $item)
          <tr class="{{ $item->status ? '' : 'table-danger' }}">
            <td class="mobile-col-sm">{{ $item->sku }}</td>
            <td class="text-capitalize">{{ $item->name }}</td>
						<td>{{ $item->cost }}</td>
						<td>{{ $item->price }}</td>
						<td class="mobile-col-lg">{{ $item->description }}</td>
						<td class="mobile-col-md">{{ $item->category ? $item->category->name : "" }}</td>
						<td class="mobile-col-sm">{{ $item->sold_by_weight ? 'Weight' : ($item->sold_by_length ? 'Length' : 'Stock') }}</td>
						<td>{{ $item->stock }}</td>
						{{-- <td class="mobile-col-md">{{ $item->status == 1 ? 'Active' : 'Disabled' }}</td> --}}
            <td>
              @if(in_array($user->type, ["admin", "stock man"]))
                <button 
                  class="btn btn-sm btn-outline-warning edit-item" 
                  data-bs-toggle="modal" 
                  data-bs-target="#itemsModal" 
                  data-id="{{ $item->id }}" 
                  data-json="{{ json_encode($item) }}">
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
              @endif
              <button 
                class="btn btn-sm btn-outline-primary barcode-item" 
                data-bs-toggle="modal" 
                data-bs-target="#barcodeModal" 
                data-sku="{{ $item->sku }}">
                <i class="fa-solid fa-barcode"></i>
              </button>
            </td>
          </tr>
        @endforeach
      @else
				<tr>
					<th colspan="10" class="text-center">No Items found.</th>
				</tr>
      @endif
    </tbody>
    @if($items && count($items))
      <tfoot>
        <tr>
          <th colspan="12" class="text-center">
            <div class="row g-3 align-items-center">
              <div class="col-auto">
                <label class="col-form-label">Select Page</label>
              </div>
              <div class="col-auto">
                <select class="form-select page-select">
                  @for($i = 1; $i <= $items->lastPage(); $i++)
                    <option value="{{ $i }}" {{ $items->currentPage() == $i ? 'selected' : '' }}>{{ $i }}</option>
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
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="/item" method="POST" id="addOrEditForm">
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <input type="hidden" name="id" />
          <div class="modal-header">
            <h5 class="modal-title" id="itemsModalLabel"><span id="type"></span> Item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="modalError" class="alert alert-danger text-center d-none" role="alert"></div>

            <div class="mb-3" id="skuField">
              <label class="form-label">SKU</label>
              <input type="text" class="form-control" name="sku" disabled autocomplete="off">
            </div>

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
									@foreach($categories as $category)
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

						<div class="mb-3" id="stockField">
              <label class="form-label">Stock</label>
              <input type="number" class="form-control" name="stock" autocomplete="off">
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

  <div class="modal fade" id="barcodeModal" tabindex="-1" aria-labelledby="barcodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div>
          <input type="hidden" name="barcodeSku"/>
          <div class="modal-header">
            <h5 class="modal-title" id="barcodeModalLabel">Barcode Item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="modalBarcodeError" class="alert alert-danger text-center d-none" role="alert"></div>
            
            <div class="mb-3">
              <label class="form-label">Number of Barcode</label>
              <input type="number" class="form-control" name="noBarcode" min="1" required autocomplete="off">
            </div>
          </div>
          <div class="modal-footer">
            <button id="generateBarcodeBtn" type="button" class="btn btn-outline-success">Print</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(function() {
      $("#selectStatus").change(function() {
        $("#searchForm").submit();
      });

      $("#clear-search").click(function() {
        $('input[name="search"]').val("");
        $("#searchForm").submit();
      });

      $('#addItem').click(function() {
        $('#type').html("Add");
        $('#skuField').hide();
        $('#stockField').hide();
        $('#modalError').html("").addClass('d-none');

        $('input[name="id"]').val("");
        $('input[name="sku"]').val("");
        $('input[name="name"]').val("");
        $('input[name="cost"]').val("");
        $('input[name="price"]').val("");
        $('textarea[name="description"]').val("");
        $('select[name="sold_by"]').val("");
        $('select[name="category"]').val("");
        $('input[name="stock"]').val(0);
        $('select[name="status"]').val("");
      });

      $('.edit-item').click(function() {
        $('#type').html("Edit");
				$('#skuField').show();
				$('#stockField').hide();
        $('#modalError').html("").addClass('d-none');
        var data = $(this).data('json');
        var soldBy = data.sold_by_weight ? "weight" : (data.sold_by_length ? 'length' : 'stock');

        $('input[name="id"]').val(data.id);
        $('input[name="sku"]').val(data.sku);
        $('input[name="name"]').val(data.name);
        $('input[name="cost"]').val(data.cost);
        $('input[name="price"]').val(data.price);
        $('textarea[name="description"]').val(data.description);
        $('select[name="sold_by"]').val(soldBy);
        $('select[name="category"]').val(data.category_id);
        $('input[name="stock"]').val(data.stock);
        $('select[name="status"]').val(data.status);
      });

      $('.delete-item').click(function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', `/item/${id}`);
      });

      $('.page-select').change(function() {
        var page = $(this).val();
        location.href = `/items?page=${page}`;
      });

      $('#addOrEditForm').submit(function(e) {
        e.preventDefault();
        $('#modalError').html("").addClass('d-none');

        var cost = $('input[name="cost"]').val();
        var price = $('input[name="price"]').val();
        if (cost > price) {
          $('#modalError').html("The cost shouldn't higher than the price.").removeClass('d-none');
          document.getElementById("itemsModal").scrollTop = 0;
        } else {
          var sku = $('input[name="sku"]').val();
          var name = $('input[name="name"]').val();
          var category = $('select[name="category"]').val();
          var skuURI = sku ? `&sku=${sku}` : '';

          $.ajax({
            type: 'GET',
            dataType: 'json',
            url: `/validate/item/category/${category}?name=${name}${skuURI}`,
            success: (data) => {
              if (data.data) {
                $('#modalError').html("Item with the same name and category is already exists.").removeClass('d-none');
                document.getElementById("itemsModal").scrollTop = 0;
              } else {
                $(this).unbind('submit').submit();
              }
            }
          });
        }
      });

      $('.barcode-item').click(function() {
        $('#modalBarcodeError').html("").addClass('d-none');
        var barcodeSku = $(this).data('sku');
        $('input[name="barcodeSku"]').val(barcodeSku);
        $('input[name="noBarcode"]').val("1");
      });

      $('#generateBarcodeBtn').click(function() {
        var sku = $('input[name="barcodeSku"]').val();
        var noBarcode = $('input[name="noBarcode"]').val();
        $('#barcodeModal').modal('hide');
        window.open(`/item/${sku}/barcode?noPrint=${noBarcode}`);
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