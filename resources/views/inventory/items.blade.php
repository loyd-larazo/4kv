@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Items</h1>

    <form class="row g-3 align-items-center" action="/items" method="GET" id="searchForm">
      <div class="col-auto row p-0 m-0 mt-3">
        <label class="form-label col-auto pt-2">Status: </label>
        <select id="selectStatus" name="status" class="form-control col">
          <option {{!isset($status) || (isset($status) && $status == 1) ? 'selected' : ''}} value="1">Active</option>
          <option {{(isset($status) && $status == 0) ? 'selected' : ''}} value="0">Disabled or 0 Stock</option>
        </select>
      </div>
      <div class="col-auto">
        <input type="text" class="form-control" placeholder="Search SKU or Item" name="search" value="{{$search}}">
      </div>
      <div class="col-auto">
        <input type="submit" class="form-control btn-outline-success" value="Search"/>
      </div>
    </form>

    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#itemsModal" id="addItem">
      Add Item
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
        <th class="mobile-col-sm" scope="col">SKU</th>
        <th scope="col">Item</th>
        <th scope="col">Cost</th>
        <th scope="col">Price</th>
        <th class="mobile-col-lg" scope="col">Description</th>
        <th class="mobile-col-md" scope="col">Category</th>
        <th class="mobile-col-sm" scope="col">Sold By</th>
        <th scope="col">Stock</th>
        <th class="mobile-col-md" scope="col">Status</th>
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
						<td class="mobile-col-md">{{ $item->status == 1 ? 'Active' : 'Disabled' }}</td>
            <td>
              <button 
                class="btn btn-sm btn-outline-warning edit-item" 
                data-bs-toggle="modal" 
                data-bs-target="#itemsModal" 
                data-id="{{ $item->id }}" 
                data-json="{{ json_encode($item) }}">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
              <a href="/item/{{ $item->sku }}/barcode" target="_blank" class="btn btn-sm btn-outline-primary barcode-item">
                <i class="fa-solid fa-barcode"></i>
              </a>
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
        <form action="/item" method="POST">
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <input type="hidden" name="id" />
          <div class="modal-header">
            <h5 class="modal-title" id="itemsModalLabel"><span id="type"></span> Item</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3" id="skuField">
              <label class="form-label">SKU</label>
              <input type="text" class="form-control" name="sku" disabled>
            </div>

            <div class="mb-3">
              <label class="form-label">Product Name</label>
              <input type="text" class="form-control" name="name" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Cost</label>
              <input type="number" class="form-control" name="cost" required>
            </div>

						<div class="mb-3">
              <label class="form-label">Price</label>
              <input type="number" class="form-control" name="price" required>
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
              <input type="number" class="form-control" name="stock" required>
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
      $("#selectStatus").change(function() {
        $("#searchForm").submit();
      });

      $('#addItem').click(function() {
        $('#type').html("Add");
        $('#skuField').hide();
        $('#stockField').show();
      });

      $('.edit-item').click(function() {
        $('#type').html("Edit");
				$('#skuField').show();
				$('#stockField').hide();
        var data = $(this).data('json');
        var soldBy = data.sold_by_weight ? "weight" : (data.sold_by_length ? 'length' : 'stock');

        $('input[name="id"]').val(data.id);
        $('input[name="sku"]').val(data.sku);
        $('input[name="name"]').val(data.name);
        $('input[name="cost"]').val(data.cost);
        $('input[name="price"]').val(data.price);
        $('textarea[name="description"]').val(data.description);
        $('select[name="sold_by"]').val(soldBy);
        $('select[name="category"]').val(data.category);
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

    });
  </script>
@endsection