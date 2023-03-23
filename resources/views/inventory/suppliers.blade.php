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
        <div class="form-control clear-input">
          <input type="text" class="form-control" placeholder="Search Supplier" name="search" value="{{$search}}" autocomplete="off">
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
        {{-- <th scope="col">Status</th> --}}
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
            {{-- <td>{{ $supplier->status == 1 ? 'Active' : 'Disabled' }}</td> --}}
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
            <div id="modalError" class="alert alert-danger text-center" role="alert"></div>

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
              <input type="number" class="form-control mobile-number" id="mobile" maxlength="11" name="contact_number" required autocomplete="off">
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
            <button type="submit" class="btn btn-outline-success" id="saveSupplier">Save</button>
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

      $('#addSupplier').click(function() {
        $('#modalError').hide();
        $('#type').html("Add");
      });

      $('.edit-supplier').click(function() {
        $('#modalError').hide();
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

      $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() }
      });

      $('input[name="name"]').change(function() {
        $('#modalError').hide();
        $('#saveSupplier').removeAttr('disabled');

        let nameVal = $(this).val();
        let idVal = $('input[name="id"]').val() || undefined;

        $.ajax({
          type: 'GET',
          dataType: 'json',
          url: `/validate/supplier?name=${nameVal}&id=${idVal}`,
          success: (data) => {
            if (data.error) {
              $('#saveSupplier').attr('disabled', true);
              $('#modalError').html(data.error).show();
              document.getElementById("supplierModal").scrollTop = 0;
            }
          }
        });
      });

      secureMobile();
      $('#mobile').change(function() {
        $('#modalError').html("").hide();
        $('#saveSupplier').removeAttr('disabled');

        let isValid = validateMobile($(this).val());
        if (!isValid) {
          $('#saveSupplier').attr('disabled', true);
          $('#modalError').html("Invalid contact number.").show();
          document.getElementById("supplierModal").scrollTop = 0;
        }
      });

      function validateMobile(num) {
        if (num[0] != '0' || num[1] != '9') {
          return false;
        }

        if (num.length != 11) {
          return false;
        }

        return true;
      }

      function secureMobile() {
        $(".mobile-number").inputFilter(function(value) {
          return /^\d*$/.test(value);    // Allow digits only, using a RegExp
        },"Only digits allowed");

        $('.mobile-number').keypress(function (e) {
          if($(e.target).prop('value').length >= 11) {
            if(e.keyCode!=32) {
              return false
            }
          } 
        });
      }
    });

    (function($) {
      $.fn.inputFilter = function(callback, errMsg) {
        return this.on("input keydown keyup mousedown mouseup select contextmenu drop focusout", function(e) {
          if (callback(this.value)) {
            // Accepted value
            if (["keydown","mousedown","focusout"].indexOf(e.type) >= 0){
              $(this).removeClass("input-error");
              this.setCustomValidity("");
            }
            this.oldValue = this.value;
            this.oldSelectionStart = this.selectionStart;
            this.oldSelectionEnd = this.selectionEnd;
          } else if (this.hasOwnProperty("oldValue")) {
            // Rejected value - restore the previous one
            $(this).addClass("input-error");
            this.setCustomValidity(errMsg);
            this.reportValidity();
            this.value = this.oldValue;
            this.setSelectionRange(this.oldSelectionStart, this.oldSelectionEnd);
          } else {
            // Rejected value - nothing to restore
            this.value = "";
          }
        });
      };
    }(jQuery));
  </script>
@endsection