@extends('app')

@section('content')
<nav class="navbar navbar-light bg-light">
  <h1>Discarded Items</h1>

  <form class="row g-3 align-items-center" action="/discard-items" method="GET">
    <div class="col-auto">
      <div class="form-control clear-input">
        <input type="text" class="form-control" placeholder="Search Discarded Item" name="search" value="{{$search}}" autocomplete="off">
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

  <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#discardModal" id="discardItem">
    Discard Item
  </button>
</nav>

@if(\Session::get('error'))
  <div class="alert alert-danger text-center" role="alert">
    {{ \Session::get('error') }}
  </div>
@endif

@if(\Session::get('success'))
  <div class="alert alert-success text-center mt-2" role="alert">
    {{ \Session::get('success') }}
  </div>
@endif

<table class="table">
  <thead>
    <tr>
      <th scope="col">Item</th>
      <th scope="col">Discarded By</th>
      <th scope="col">Supplier</th>
      <th scope="col">Price</th>
      <th scope="col">Quantity</th>
      <th scope="col">Total Price</th>
      <th scope="col">Date</th>
    </tr>
  </thead>

  <tbody>
    @if(isset($discardItems) && count($discardItems))
      @foreach($discardItems as $discardItem)
        <?php
          $fname = $discardItem->user->firstname ? $discardItem->user->firstname : '';
          $lname = $discardItem->user->lastname ? $discardItem->user->lastname : '';
          $discardedBy = $fname." ".$lname;

          $strDate = strtotime($discardItem->created_at);
          $transDate = getDate($strDate);
          $discardedDate = $transDate['month']." ".$transDate['mday'].", ".$transDate['year'];
        ?>
        <tr>
          <td class="mobile-col-md">{{ $discardItem->item->name }}</td>
          <td class="mobile-col-md">{{ $discardedBy }}</td>
          <td class="mobile-col-md">{{ $discardItem->supplier->name }}</td>
          <td>P{{ number_format($discardItem->amount) }}</td>
          <td>{{ $discardItem->quantity }}</td>
          <td>P{{ number_format($discardItem->total_amount) }}</td>
          <td>{{ $discardedDate }}</td>
        </tr>
      @endforeach
    @else
      <tr>
        <th colspan="7" class="text-center">No discarded items found.</th>
      </tr>
    @endif
  </tbody>

  @if(isset($discardItems) && count($discardItems))
    <tfoot>
      <tr>
        <th colspan="12" class="text-center">
          <div class="row g-3 align-items-center">
            <div class="col-auto">
              <label class="col-form-label">Select Page</label>
            </div>
            <div class="col-auto">
              <select class="form-select page-select">
                @for($i = 1; $i <= $discardItems->lastPage(); $i++)
                  <option value="{{ $i }}" {{ $discardItems->currentPage() == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
              </select>
            </div>
          </div>
        </th>
      </tr>
    </tfoot>
  @endif
</table>

<div class="modal fade" id="discardModal" tabindex="-1" aria-labelledby="discardModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="discardForm" action="/discard-item" method="POST">
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
        <input type="hidden" name="id" />
        <div class="modal-header">
          <h5 class="modal-title" id="discardModalLabel"><span id="type"></span> Discard Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="modalError alert alert-danger text-center d-none" role="alert"></div>

          <div class="mb-3">
            <label class="form-label">Item</label>
            <select class="form-select" name="item" required>
              <option disabled selected value>Select Item</option>
              @if(isset($items) && count($items))
                @foreach($items as $item)
                  <?php 
                    $val = json_encode([
                      'id' => $item->id, 
                      'stock' => $item->stock,
                      'amount' => $item->cost
                    ]); 
                  ?>
                  <option value="{{ $val }}">{{ $item->name }}</option>
                @endforeach
              @endif
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Supplier</label>
            <select class="form-select" name="supplier" required disabled>
              <option disabled selected value>Select Supplier</option>
              @if(isset($suppliers) && count($suppliers))
                @foreach($suppliers as $supplier)
                  <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                @endforeach
              @endif
            </select>
          </div>

          <div class="mb-3 form-group">
            <label class="form-label">Quantity</label> <small class="text-muted" name="stock"></small>
            <input type="number" name="qty" value="1" min="1" class="form-control" autocomplete="off" required disabled>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-outline-success">Discard</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  $(function() {
    $("#clear-search").click(function() {
      $('input[name="search"]').val("");
      $("#searchForm").submit();
    });

    $('.page-select').change(function() {
      search();
    });

    function search(p, d) {
      var page = p || $('.page-select').val();
      location.href = `/discard-items?page=${page}`;
    }

    $('select[name="item"]').change(function() {
      $val = JSON.parse($(this).val());
      $('input[name="qty"]')
        .attr('max', $val.stock)
        .removeAttr('disabled');

      $('small[name="stock"]').html(`Total stocks: ${$val.stock}`);
      $('select[name="supplier"]').removeAttr('disabled');

      $('input[name="qty"]').on('keydown change keyup click', function() {
        if ($(this).val() > $val.stock) {
          $('#discardModal .modalError')
            .html('Not enough stocks.')
            .removeClass('d-none');
        } else {
          $('#discardModal .modalError').html('').addClass('d-none');
        }
      });
    });

    $('#discardItem').on('click', function() {
      $('select[name="item"]').val('');
      $('select[name="supplier"]').val('');
      $('small[name="stock"]').html('');
      $('input[name="qty"]').val(1);
    });
  });
</script>
@endsection