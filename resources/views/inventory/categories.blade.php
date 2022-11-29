@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Categories</h1>

    <form class="row g-3 align-items-center" action="/categories" method="GET">
      <div class="col-auto">
        <input type="text" class="form-control" placeholder="Search Category" name="search" value="{{$search}}">
      </div>
      <div class="col-auto">
        <input type="submit" class="form-control btn-outline-success" value="Search"/>
      </div>
    </form>

    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" id="addCategory">
      Add Category
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
        <th scope="col">Status</th>
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
      @if($categories && count($categories))
        @foreach($categories as $category)
          <tr class="{{ $category->status ? '' : 'table-danger' }}">
            <td class="text-capitalize">{{ $category->name }}</td>
            <td>{{ $category->status == 1 ? 'Acitve' : 'Disabled' }}</td>
            <td>
              <button 
                class="btn btn-sm btn-outline-warning edit-category" 
                data-bs-toggle="modal" 
                data-bs-target="#categoryModal" 
                data-id="{{ $category->id }}" 
                data-json="{{ json_encode($category) }}">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>

              <button 
                class="btn btn-sm btn-outline-danger delete-category" 
                data-bs-toggle="modal" 
                data-bs-target="#deletecategoryModal" 
                data-id="{{ $category->id }}" >
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </td>
          </tr>
        @endforeach
      @else
          <tr>
            <th colspan="7" class="text-center">No Category found.</th>
          </tr>
      @endif
    </tbody>
    @if($categories && count($categories))
      <tfoot>
        <tr>
          <th colspan="12" class="text-center">
            <div class="row g-3 align-items-center">
              <div class="col-auto">
                <label class="col-form-label">Select Page</label>
              </div>
              <div class="col-auto">
                <select class="form-select page-select">
                  @for($i = 1; $i <= $categories->lastPage(); $i++)
                    <option value="{{ $i }}" {{ $categories->currentPage() == $i ? 'selected' : '' }}>{{ $i }}</option>
                  @endfor
                </select>
              </div>
            </div>
          </th>
        </tr>
      </tfoot>
    @endif
  </table>

  <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="/category" method="POST">
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <input type="hidden" name="id" />
          <div class="modal-header">
            <h5 class="modal-title" id="categoryModalLabel"><span id="type"></span> Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" class="form-control" name="name" required>
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

  <div class="modal fade" id="deletecategoryModal" tabindex="-1" aria-labelledby="deletecategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <form id="deleteForm" action="/category" method="POST">
          @method('delete')
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <input type="hidden" name="id" />
          <div class="modal-header">
            <h5 class="modal-title" id="deletecategoryModalLabel">Delete Category</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete this category?
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-outline-danger">Yes</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    $(function() {
      $('#addCategory').click(function() {
        $('#type').html("Add");
      });

      $('.edit-category').click(function() {
        $('#type').html("Edit");
        var data = $(this).data('json');

        $('input[name="id"]').val(data.id);
        $('input[name="name"]').val(data.name);
        $('select[name="status"]').val(data.status);
      });

      $('.delete-category').click(function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', `/category/${id}`);
      });

      $('.page-select').change(function() {
        var page = $(this).val();
        location.href = `/categories?page=${page}`;
      });

    });
  </script>
@endsection