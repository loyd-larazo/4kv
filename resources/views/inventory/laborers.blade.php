@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Laborers</h1>

    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#laborerModal" id="addLaborer">
      Add Laborer
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
        <th scope="col">Gender</th>
        <th scope="col">Birthdate</th>
        <th scope="col">Address</th>
        <th scope="col">Contact Number</th>
        <th scope="col">Salary</th>
        <th scope="col">Position</th>
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
      @if($laborers && count($laborers))
        @foreach($laborers as $laborer)
          <tr>
            <td class="text-capitalize">{{ $laborer->firstname .' '. $laborer->lastname }}</td>
            <td class="text-capitalize">{{ $laborer->gender }}</td>
            <td>{{ $laborer->birthdate }}</td>
            <td>{{ $laborer->address }}</td>
            <td>{{ $laborer->contact_number }}</td>
            <td>{{ $laborer->salary }}</td>
            <td class="text-capitalize">{{ $laborer->position }}</td>
            <td>
              <button 
                class="btn btn-sm btn-outline-warning edit-laborer" 
                data-bs-toggle="modal" 
                data-bs-target="#laborerModal" 
                data-id="{{ $laborer->id }}" 
                data-json="{{ json_encode($laborer) }}">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>

              {{-- <button 
                class="btn btn-sm btn-outline-danger delete-laborer" 
                data-bs-toggle="modal" 
                data-bs-target="#deleteLaborerModal" 
                data-id="{{ $laborer->id }}" >
                <i class="fa-solid fa-trash-can"></i>
              </button> --}}
            </td>
          </tr>
        @endforeach
      @else
          <tr>
            <th colspan="7" class="text-center">No Laborers found.</th>
          </tr>
      @endif
    </tbody>
    @if($laborers && count($laborers))
      <tfoot>
        <tr>
          <th colspan="12" class="text-center">
            <div class="row g-3 align-items-center">
              <div class="col-auto">
                <label class="col-form-label">Select Page</label>
              </div>
              <div class="col-auto">
                <select class="form-select page-select">
                  @for($i = 1; $i <= $laborers->lastPage(); $i++)
                    <option value="{{ $i }}" {{ $laborers->currentPage() == $i ? 'selected' : '' }}>{{ $i }}</option>
                  @endfor
                </select>
              </div>
            </div>
          </th>
        </tr>
      </tfoot>
    @endif
  </table>

  <div class="modal fade" id="laborerModal" tabindex="-1" aria-labelledby="laborerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="/laborer" method="POST">
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <input type="hidden" name="id" />
          <div class="modal-header">
            <h5 class="modal-title" id="laborerModalLabel"><span id="type"></span> Laborer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">First Name</label>
              <input type="text" class="form-control" name="firstname" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Last Name</label>
              <input type="text" class="form-control" name="lastname" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Gender</label>
              <select id="disabledSelect" class="form-select" name="gender" required>
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Birthdate</label>
              <input type="date" class="form-control" name="birthdate" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Contact Number</label>
              <input type="text" class="form-control" name="contact_number" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Address</label>
              <textarea class="form-control" name="address" required></textarea>
            </div>

            <div class="mb-3">
              <label class="form-label">Salary</label>
              <input type="number" class="form-control" name="salary" required>
            </div>
            
            <div class="mb-3">
              <label class="form-label">Position</label>
              <input type="text" class="form-control" name="position" required>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="deleteLaborerModal" tabindex="-1" aria-labelledby="deleteLaborerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <form id="deleteForm" action="/laborer" method="POST">
          @method('delete')
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <input type="hidden" name="id" />
          <div class="modal-header">
            <h5 class="modal-title" id="deleteLaborerModalLabel">Delete Laborer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete this laborer?
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
      $('#addLaborer').click(function() {
        $('#type').html("Add");
      });

      $('.edit-laborer').click(function() {
        $('#type').html("Edit");
        var data = $(this).data('json');

        $('input[name="id"]').val(data.id);
        $('input[name="firstname"]').val(data.firstname);
        $('input[name="lastname"]').val(data.lastname);
        $('select[name="gender"]').val(data.gender);
        $('input[name="birthdate"]').val(data.birthdate);
        $('input[name="contact_number"]').val(data.contact_number);
        $('textarea[name="address"]').val(data.address);
        $('input[name="salary"]').val(data.salary);
        $('input[name="position"]').val(data.position);
      });

      $('.delete-laborer').click(function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', `/laborer/${id}`);
      });

      $('.page-select').change(function() {
        var page = $(this).val();
        location.href = `/laborers?page=${page}`;
      });

    });
  </script>
@endsection