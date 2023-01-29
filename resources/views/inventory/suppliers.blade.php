@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Suppliers</h1>

    <form class="row g-3 align-items-center" action="/suppliers" method="GET" id="searchForm">
      <div class="col-auto row p-0 m-0 mt-3">
        <label class="form-label col-auto pt-2">Status: </label>
        <select id="selectStatus" name="status" class="form-control col">
          <option {{!isset($status) || (isset($status) && $status == 1) ? 'selected' : ''}} value="1">Active</option>
          <option {{(isset($status) && $status == 0) ? 'selected' : ''}} value="0">Disabled</option>
        </select>
      </div>
      <div class="col-auto">
        <input type="text" class="form-control" placeholder="Search Supplier" name="search" value="{{$search}}" autocomplete="off">
      </div>
      <div class="col-auto">
        <input type="submit" class="form-control btn-outline-success" value="Search"/>
      </div>
    </form>

    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#supplierModal" id="addSupplier">
      Add Supplier
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
        <th scope="col">Name</th>
        <th scope="col">Contact Person</th>
        <th class="mobile-col-sm" scope="col">Contact Number</th>
        <th class="mobile-col-md" scope="col">Address</th>
        <th scope="col">Status</th>
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
      @if($suppliers && count($suppliers))
        @foreach($suppliers as $supplier)
          <tr class="{{ $supplier->status ? '' : 'table-danger' }}">
            <td class="text-capitalize">{{ $supplier->name }}</td>
            <td class="text-capitalize">{{ $supplier->contact_person }}</td>
            <td class="mobile-col-sm">{{ $supplier->contact_number }}</td>
            <td class="mobile-col-md">{{ $supplier->address }}</td>
            <td>{{ $supplier->status == 1 ? 'Active' : 'Disabled' }}</td>
            <td>
              <button 
                class="btn btn-sm btn-outline-warning edit-supplier" 
                data-bs-toggle="modal" 
                data-bs-target="#supplierModal" 
                data-id="{{ $supplier->id }}" 
                data-json="{{ json_encode($supplier) }}">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>
            </td>
          </tr>
        @endforeach
      @else
          <tr>
            <th colspan="7" class="text-center">No Suppliers found.</th>
          </tr>
      @endif
    </tbody>
    @if($suppliers && count($suppliers))
      <tfoot>
        <tr>
          <th colspan="12" class="text-center">
            <div class="row g-3 align-items-center">
              <div class="col-auto">
                <label class="col-form-label">Select Page</label>
              </div>
              <div class="col-auto">
                <select class="form-select page-select">
                  @for($i = 1; $i <= $suppliers->lastPage(); $i++)
                    <option value="{{ $i }}" {{ $suppliers->currentPage() == $i ? 'selected' : '' }}>{{ $i }}</option>
                  @endfor
                </select>
              </div>
            </div>
          </th>
        </tr>
      </tfoot>
    @endif
  </table>

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
              <input type="text" class="form-control" name="name" required autocomplete="nope">
            </div>

            <div class="mb-3">
              <label class="form-label">Contact Person</label>
              <input type="text" class="form-control" name="contact_person" required autocomplete="nope">
            </div>

            <div class="mb-3">
              <label class="form-label">Contact Number</label>
              <input type="number" class="form-control" name="contact_number" required autocomplete="nope">
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
      $("#selectStatus").change(function() {
        $("#searchForm").submit();
      });

      $('#addSupplier').click(function() {
        $('#type').html("Add");
      });

      $('.edit-supplier').click(function() {
        $('#type').html("Edit");
        var data = $(this).data('json');

        $('input[name="id"]').val(data.id);
        $('input[name="name"]').val(data.name);
        $('input[name="contact_person"]').val(data.contact_person);
        $('input[name="contact_number"]').val(data.contact_number);
        $('textarea[name="address"]').val(data.address);
        $('select[name="status"]').val(data.status);
      });

      $('.page-select').change(function() {
        var page = $(this).val();
        location.href = `/suppliers?page=${page}`;
      });

    });
  </script>
@endsection