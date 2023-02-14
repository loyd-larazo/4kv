@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Users</h1>

    <form class="row g-3 align-items-center" action="/users" method="GET" id="searchForm">
      <div class="col-auto row p-0 m-0 mt-3">
        <label class="form-label col-auto pt-2">Status: </label>
        <select id="selectStatus" name="status" class="form-control col">
          <option {{!isset($status) || (isset($status) && $status == 1) ? 'selected' : ''}} value="1">Active</option>
          <option {{(isset($status) && $status == 0) ? 'selected' : ''}} value="0">Disabled</option>
        </select>
      </div>
      <div class="col-auto">
        <div class="form-control clear-input">
          <input type="text" class="form-control" placeholder="Search User" name="search" value="{{$search}}" autocomplete="off">
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

    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#userModal" id="addUser">
      Add User
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
        <th scope="col">Username</th>
        <th scope="col">Type</th>
        <th class="mobile-col-md" scope="col">Address</th>
        <th class="mobile-col-md" scope="col">Contact Number</th>
        {{-- <th class="mobile-col-sm" scope="col">Status</th> --}}
        <th scope="col"></th>
      </tr>
    </thead>
    <tbody>
      @if($users && count($users))
        @foreach($users as $user)
          <?php 
            $birthdate = "";
            if ($user->birthdate) {
              $strDate = strtotime($user->birthdate);
              $transDate = getDate($strDate);
              $birthdate = $transDate['month']." ".$transDate['mday'].", ".$transDate['year'];
            }
          ?>
          <tr class="{{ $user->status ? '' : 'table-danger' }}">
            <td class="text-capitalize">{{ $user->firstname .' '. $user->lastname }}</td>
            <td>{{ $user->username }}</td>
            <td class="text-capitalize">{{ $user->type }}</td>
            <td class="mobile-col-md">{{ $user->address }}</td>
            <td class="mobile-col-md">{{ $user->contact_number }}</td>
            {{-- <td class="mobile-col-sm">{{ $user->status == 1 ? 'Active' : 'Disabled' }}</td> --}}
            <td>
              <button 
                class="btn btn-sm btn-outline-warning edit-user" 
                data-bs-toggle="modal" 
                data-bs-target="#userModal" 
                data-id="{{ $user->id }}" 
                data-json="{{ json_encode($user) }}">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>

              {{-- <button 
                class="btn btn-sm btn-outline-danger delete-user" 
                data-bs-toggle="modal" 
                data-bs-target="#deleteuserModal" 
                data-id="{{ $user->id }}" >
                <i class="fa-solid fa-trash-can"></i>
              </button> --}}
            </td>
          </tr>
        @endforeach
      @else
          <tr>
            <th colspan="7" class="text-center">No User found.</th>
          </tr>
      @endif
    </tbody>
    @if($users && count($users))
      <tfoot>
        <tr>
          <th colspan="12" class="text-center">
            <div class="row g-3 align-items-center">
              <div class="col-auto">
                <label class="col-form-label">Select Page</label>
              </div>
              <div class="col-auto">
                <select class="form-select page-select">
                  @for($i = 1; $i <= $users->lastPage(); $i++)
                    <option value="{{ $i }}" {{ $users->currentPage() == $i ? 'selected' : '' }}>{{ $i }}</option>
                  @endfor
                </select>
              </div>
            </div>
          </th>
        </tr>
      </tfoot>
    @endif
  </table>

  <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="/user" method="POST" id="userForm">
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <input type="hidden" name="id" />
          <div class="modal-header">
            <h5 class="modal-title" id="userModalLabel"><span id="type"></span> User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3" id="changePasswordParent">
              <input type="checkbox" id="changePassword" name="change_password" autocomplete="off"/>
              <label class="form-label" for="changePassword">Change Password</label>
            </div>

            <div id="updatePassword" class="d-none">
              <div class="mb-3">
                <label class="form-label">New Password</label>
                <input type="password" class="form-control required" name="new_password" autocomplete="off">
              </div>
              <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" class="form-control required" name="confirm_password" autocomplete="off">
              </div>
            </div>

            <div id="updateInfo">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control required" name="username" required autocomplete="off">
              </div>

              <div class="mb-3" id="addPassword">
                <label class="form-label">Password</label>
                <input type="password" class="form-control required" name="password" autocomplete="off">
              </div>
              
              <div class="mb-3">
                <label class="form-label">Type</label>
                <select id="disabledSelect" class="form-select required" name="type" required>
                  <option value="">Select Type</option>
                  <option value="admin">Admin</option>
                  <option value="cashier">Cashier</option>
                  <option value="stock man">Stock Man</option>
                </select>
              </div>
  
              <div class="mb-3">
                <label class="form-label">First Name</label>
                <input type="text" class="form-control required" name="firstname" required autocomplete="off">
              </div>
  
              <div class="mb-3">
                <label class="form-label">Last Name</label>
                <input type="text" class="form-control required" name="lastname" required autocomplete="off">
              </div>
  
              <div class="mb-3">
                <label class="form-label">Gender</label>
                <select id="disabledSelect" class="form-select required" name="gender" required>
                  <option value="">Select Gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                </select>
              </div>
  
              <div class="mb-3">
                <label class="form-label">Birthdate</label>
                <input type="date" class="form-control required" name="birthdate" required autocomplete="off">
              </div>
  
              <div class="mb-3">
                <label class="form-label">Contact Number</label>
                <input type="text" class="form-control required" name="contact_number" required autocomplete="off">
              </div>
  
              <div class="mb-3">
                <label class="form-label">Address</label>
                <textarea class="form-control required" name="address" required autocomplete="off"></textarea>
              </div>
  
              <div class="mb-3">
                <label class="form-label">Salary</label>
                <input type="number" class="form-control required" name="salary" required autocomplete="off">
              </div>
  
              <div class="mb-3">
                <label class="form-label">Status</label>
                <select class="form-select required" name="status" required>
                  <option value="">Select Option</option>
                  <option value="1">Active</option>
                  <option value="0">Disabled</option>
                </select>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-outline-success">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="modal fade" id="deleteuserModal" tabindex="-1" aria-labelledby="deleteuserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <form id="deleteForm" action="/user" method="POST">
          @method('delete')
          <input type="hidden" name="_token" value="{{ csrf_token() }}" />
          <input type="hidden" name="id" />
          <div class="modal-header">
            <h5 class="modal-title" id="deleteuserModalLabel">Delete User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            Are you sure you want to delete this user?
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
      $("#selectStatus").change(function() {
        $("#searchForm").submit();
      });

      $("#clear-search").click(function() {
        $('input[name="search"]').val("");
        $("#searchForm").submit();
      });

      const today = new Date();
      const minYear = (today.getFullYear()).toString() - 100;
      $('input[name="birthdate"]').attr("min", minYear+"-01-01");

      $("#changePassword").change(function() {
        resetForm();
      });

      $('#addUser').click(function() {
        $('#type').html("Add");
        resetForm(true);
        $('#changePasswordParent').addClass("d-none");
        $("#addPassword").removeClass("d-none").children('input').attr("required", true);
      });

      $('.edit-user').click(function() {
        $('#type').html("Edit");
        resetForm(true);
        $('#changePasswordParent').removeClass("d-none");
        $("#addPassword").addClass("d-none").children('input').removeAttr("required");
        
        var data = $(this).data('json');

        $('input[name="id"]').val(data.id);
        $('input[name="username"]').val(data.username);
        $('select[name="type"]').val(data.type);
        $('input[name="firstname"]').val(data.firstname);
        $('input[name="lastname"]').val(data.lastname);
        $('select[name="gender"]').val(data.gender);
        $('input[name="birthdate"]').val(data.birthdate);
        $('input[name="contact_number"]').val(data.contact_number);
        $('textarea[name="address"]').val(data.address);
        $('input[name="salary"]').val(data.salary);
        $('select[name="status"]').val(data.status);
      });

      $('.delete-user').click(function() {
        var id = $(this).data('id');
        $('#deleteForm').attr('action', `/user/${id}`);
      });

      $('.page-select').change(function() {
        var page = $(this).val();
        location.href = `/users?page=${page}`;
      });

    });

    $('#userForm').submit(function(event) {
      if ($("#changePassword").is(':checked')) {
        event.preventDefault();
      
        var newPassword = $('input[name="new_password"]').val();
        var confirmPassword = $('input[name="confirm_password"]').val();
        if (newPassword != confirmPassword) {
          alert("Password not matched!");
        } else {
          $(this).unbind('submit').submit();
        }
      }
    })

    function resetForm(uncheck) {
      if (uncheck) {
        $('#changePassword').prop("checked", false);
      }

      if (uncheck !== undefined) {
        $('input[name="id"]').val("");
        $('input[name="username"]').val("");
        $('select[name="type"]').val("");
        $('input[name="firstname"]').val("");
        $('input[name="lastname"]').val("");
        $('select[name="gender"]').val("");
        $('input[name="birthdate"]').val("");
        $('input[name="contact_number"]').val("");
        $('textarea[name="address"]').val("");
        $('input[name="salary"]').val("");
        $('select[name="status"]').val("");
      }

      if ($("#changePassword").is(':checked')) {
        $('#updatePassword').removeClass('d-none');
        $('#updateInfo').addClass('d-none');
        $('#updatePassword .required').attr('required', 'true');
        $('#updateInfo .required').removeAttr('required');
      } else {
        $('#updatePassword').addClass('d-none');
        $('#updateInfo').removeClass('d-none');
        $('#updateInfo .required').attr('required', 'true');
        $('#updatePassword .required').removeAttr('required');
      }
    }
  </script>
@endsection