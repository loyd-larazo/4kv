@extends('app')

@section('content')
  <nav class="navbar navbar-light bg-light">
    <h1>Reports</h1>
    <div class="row g-3 align-items-center" id="getReportForm">
      <div class="col-auto row p-0 m-0 mt-3">
        <label class="form-label col-auto pt-2">Type of Report: </label>
        <select id="selectReportType" name="reportType" class="form-control col">
          <option {{(!isset($type)) ? 'selected' : ''}} disabled>Select report</option>
          <option {{(isset($type) && $type == "topSelling") ? 'selected' : ''}} value="topSelling">Top Selling Items</option>
          <option {{(isset($type) && $type == "soldItems") ? 'selected' : ''}} value="soldItems">Sold Items</option>
          <option {{(isset($type) && $type == "inventory") ? 'selected' : ''}} value="inventory">Inventory List</option>
          <option {{(isset($type) && $type == "dailySales") ? 'selected' : ''}} value="dailySales">Daily Sales</option>
          <option {{(isset($type) && $type == "damageItems") ? 'selected' : ''}} value="damageItems">Damage Items</option>
          <option {{(isset($type) && $type == "lowStock") ? 'selected' : ''}} value="lowStock">Low Stock Items</option>
          <option {{(isset($type) && $type == "transaction") ? 'selected' : ''}} value="transaction">Transaction List</option>
          <option {{(isset($type) && $type == "sales") ? 'selected' : ''}} value="sales">Sales List</option>
        </select>
      </div>
      <div class="col-auto">
        <input id="selectFilter" type="button" class="form-control btn-outline-primary" value="Select Filter" autocomplete="off"/>
      </div>
    </div>
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

  <div class="row d-flex justify-content-between">
    <div id="filterConfirm" class="p-3 col-10 filter"></div>
    <div class="col-auto d-flex align-items-center">
      <input id="onLoad" type="button" class="form-control btn-outline-success filter" value="Load Data" autocomplete="off"/>
    </div>
    <div id="printBtnContainer"></div>
    <hr>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead id="thead"></thead>
      <tbody id="tbody"></tbody>
    </table>
  </div>

  <div class="modal fade" id="filterRptModal" tabindex="-1" aria-labelledby="filterRptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content">
        <div id="filterRptForm">
          <div class="modal-header">
            <h5 class="modal-title" id="filterRptModalLabel">Add Filter to Report</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="modalErrAlert" class="alert alert-danger text-center" role="alert"></div>
            <div class="row mb-3">
              <label class="col-auto d-flex align-items-center">Date Range of Report: </label>
              <div class="col-7"><input type="text" id="daterange" class="form-control"/></div>
            </div>
            <div>
              <label class="form-label">Check Columns to Include: </label>
              <li id="includeAll" class="form-check">
                <input id="itemAll" type="checkbox" class="form-check-input">
                <label class="form-check-label text-capitalize" for="itemAll">Include All</label>
              </li>
              <ul id="columnOptionsContainer" class="list-unstyled"></ul>
            </div>
          </div>
          <div class="modal-footer">
            <button id="addFilterBtn" type="button" class="btn btn-outline-primary">Add</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(function() {
      let columnList = null;
      let filterData = null;
      let dbCols = @json($dbCols);
      let currentReportType = '';

      $('.filter').hide();
      $('#modalErrAlert').hide();
      $('#selectReportType').change(function() {
        filterData = null;
        $('.filter').hide();
        $('#thead').html('');
        $("#tbody").html('');
        $('.print').hide();
        $('#printBtnContainer').html('').removeClass('col-auto d-flex align-items-center filter print');
        $('#itemAll').prop('checked', false);
      });

      // Populate data on modal
      $('#selectFilter').click(function() {
        let reportType = $('#selectReportType').val();

        if (reportType != currentReportType) {
          currentReportType = reportType;
          setDatePicker();
          populateColumnOptions(reportType);
        }

        $('#filterRptModal').modal('show');
      });

      function setDatePicker() {
        let startDate = new Date();
        let endDate = new Date();

        if (filterData) {
          startDate = new Date(filterData.sDate);
          endDate = new Date(filterData.eDate);
        }

        $('#daterange').daterangepicker({
          dateFormat: 'mm/dd/yy', 
          startDate, 
          endDate,
          ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
          }
        });
      }

      function populateColumnOptions(type) {
        let columns = @json($columns);
        columnList = columns[type];
        let columnContainer = $('#columnOptionsContainer');
        columnContainer.empty();

        for (key in columnList) {
          let value = columnList[key].replace(/_/g, " ");
          const listItem = $(`<li class="form-check">`);
          listItem.html(`
            <input id="item${key}" type="checkbox" data-index="${key}" class="form-check-input column-option">
            <label class="form-check-label text-capitalize" for="item${key}">${value}</label>
          `);
          columnContainer.append(listItem);
        }

        if (columnList) {
          $('#includeAll').show();

          $('.column-option').change(function() {
            let uncheckedExist = $('.column-option:not(:checked)').length > 0;
            $('#itemAll').prop('checked', !uncheckedExist);
          });
        } else $('#includeAll').hide();
      }

      $('#itemAll').change(function() {
        let isChecked = $(this).prop('checked');
        $('.column-option').prop('checked', isChecked);
      });

      // Get filter data from modal
      $('#addFilterBtn').click(function() {
        const picker = $("#daterange").data("daterangepicker");
        const startDate = picker.startDate.format("YYYY-MM-DD");
        const endDate = picker.endDate.format("YYYY-MM-DD");
        const dateWord = `${picker.startDate.format('MMM D, YYYY')} to ${picker.endDate.format('MMM D, YYYY')}`;

        let checkedItemsIndex = [];
        let checkedItemsWord = [];
        let checkedInput = $("#columnOptionsContainer input:checked");

        if ($("#columnOptionsContainer input:checked").length <= 0) {
          $('#modalErrAlert').html('Please check columns to include to the report.').show();
          return;
        }

        checkedInput.map(function() {
          let index = $(this).data('index');
          checkedItemsIndex.push(index);
          checkedItemsWord.push(columnList[index].replace(/_/g, " "));
        });

        let selectedRptTypeLabel = $('#selectReportType option:selected').text();
        let confirmMsg = `
          <i>
            Please check all the filters you've added for the ${selectedRptTypeLabel} report.
            Dated from ${dateWord}.
            While the columns are ${checkedItemsWord.join(', ')}.
          </i>
        `;

        filterData = {
          rptType: $('#selectReportType').val(),
          sDate: startDate,
          eDate: endDate, 
          items: checkedItemsIndex
        };

        $('#filterConfirm').html(confirmMsg);
        $('.filter').show();
        $('.print').hide();
        $('#printBtnContainer').html('').removeClass('col-auto d-flex align-items-center filter print');
        $('#modalErrAlert').hide();
        $('#filterRptModal').modal('hide');
      });

      $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('input[name="_token"]').val() }
      });

      $('#onLoad').click(function() {
        if (filterData) {
          let url = `/reports/load?rptType=${filterData.rptType}`
          url += `&sDate=${filterData.sDate}&eDate=${filterData.eDate}`;

          $.ajax({
            type: 'GET',
            dataType: 'json',
            url,
            success: (data) => {
              $('#thead').html('');
              $("#tbody").html('');
              $('.print').hide();
              $('#printBtnContainer').html('').removeClass('col-auto d-flex align-items-center filter print');
              
              let items = data.data;
              let grandTotal = data.grandTotal;
              let headerHtml = '<tr>';
              filterData.items.map(key => {
                let col = columnList[key];
                headerHtml += `
                  <th class="text-capitalize">${col.replace(/_/g, " ")}</th>
                `;
              });
              headerHtml += "</tr>";
              $('#thead').html(headerHtml);
              
              if (items && items.length) {
                $('.print').show();
                injectPrintBtn();

                let currentColumns = dbCols[currentReportType];
                let bodyHtml = "";
                let footerHtml = "<tr>";
                
                items.map((item, index) => {
                  bodyHtml += "<tr>";
                  filterData.items.map(key => {
                    let col = columnList[key];
                    let dbCol = currentColumns[col];
                    let dbColNames = dbCol.split(".");
                    let itemVal = '';
                    let grandVal = '';

                    for (let d in dbColNames) {
                      let dbColName = dbColNames[d];
                      itemVal = itemVal ? itemVal[dbColName] : item[dbColName];
                      if (grandTotal) grandVal = grandTotal[dbColName];
                    }
                    
                    if (col.includes('price') || col.includes('cost') || col.includes('amount') || col.includes('discrepancy'))
                      itemVal = `P${parseFloat(itemVal).toFixed(2)}`;
                    if (col == 'date')
                      itemVal = new Date(itemVal).toLocaleDateString("en-US", { month: "long", day: "numeric", year: "numeric" });
                    if (col.includes('sold_by'))
                      itemVal = itemVal ? 'Yes' : 'No';
                    if (itemVal == null)
                      itemVal = '';
                    
                    bodyHtml += `<td class="mobile-col-sm">${itemVal}</td>`;

                    if (grandTotal && index == 0) {
                      let grandTotalVal = '';
                      if (grandVal || grandVal == 0) grandTotalVal = `P${parseFloat(grandVal).toFixed(2)}`;
                      footerHtml += `<td class="mobile-col-sm"><strong>${grandTotalVal}</strong></td>`;
                    }
                  });
                  bodyHtml += "</tr>";
                });

                if (grandTotal) {
                  footerHtml += "</tr>";
                  bodyHtml += footerHtml;
                } else footerHtml = '';

                $("#tbody").html(bodyHtml);
              }
            }
          });
        }
      });

      function injectPrintBtn() {
        let path = `/report/${currentReportType}/print`;
        path += `?sDate=${filterData.sDate}&eDate=${filterData.eDate}&items=${filterData.items}`;
        let btn = `
          <a href="${path}" target="_blank" id="printBtn"
          class="btn btn-outline-info form-control filter print">
          Print Report</a>
        `;
        $('#printBtnContainer').html(btn).addClass('col-auto d-flex align-items-center filter print');
      }
    });
  </script>
@endsection