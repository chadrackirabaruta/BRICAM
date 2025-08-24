@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1><i class="bi bi-truck"></i> Transport Records</h1>
    <button id="printBtn" class="btn btn-primary">
      <i class="bi bi-printer"></i> Print Report
    </button>
  </div>

  @include('theme.success')

  {{-- FILTERS --}}
  <div class="card p-3 mb-4">
    <form method="GET" id="filterForm" class="row g-3 align-items-end">
      <div class="col-md-3 col-6">
        <label class="form-label"><strong>Start Date</strong></label>
        <input type="date" name="start_date" class="form-control" 
               value="{{ request('start_date', now()->subWeek()->toDateString()) }}" 
               max="{{ now()->toDateString() }}" id="startDate">
      </div>
      <div class="col-md-3 col-6">
        <label class="form-label"><strong>End Date</strong></label>
        <input type="date" name="end_date" class="form-control" 
               value="{{ request('end_date', now()->toDateString()) }}" 
               max="{{ now()->toDateString() }}" id="endDate">
      </div>
      <div class="col-md-4 col-8">
        <label class="form-label"><strong>Umukozi</strong></label>
        <select name="employee_id" class="form-select select2">
          <option value="">-- Abakozi bose --</option>
          @foreach($employees as $emp)
            <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
              {{ $emp->name }}
            </option>
          @endforeach
        </select>
      </div>
      <div class="col-md-2 col-4 d-grid">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-funnel"></i> Filter
        </button>
      </div>
    </form>
  </div>

  {{-- SUMMARY CARDS --}}
  <div class="row mb-4">
    <div class="col-xl-3 col-md-6">
      <div class="card info-card revenue-card">
        <div class="card-body">
          <h5 class="card-title">Total Records</h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
              <i class="bi bi-list-check"></i>
            </div>
            <div class="ps-3">
              <h6>{{ $totalRecords ?? 0 }}</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="card info-card customers-card">
        <div class="card-body">
          <h5 class="card-title">Total Employees</h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
              <i class="bi bi-people"></i>
            </div>
            <div class="ps-3">
              <h6>{{ $totalEmployees ?? 0 }}</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="card info-card sales-card">
        <div class="card-body">
          <h5 class="card-title">Total Quantity</h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
              <i class="bi bi-box-seam"></i>
            </div>
            <div class="ps-3">
              <h6>{{ number_format($totalQuantity ?? 0) }}</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-md-6">
      <div class="card info-card revenue-card">
        <div class="card-body">
          <h5 class="card-title">Total Amount</h5>
          <div class="d-flex align-items-center">
            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
              <i class="bi bi-currency-exchange"></i>
            </div>
            <div class="ps-3">
              <h6>{{ number_format($grandTotal ?? 0) }} Rwf</h6>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- MAIN TABLE --}}
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="card-title mb-0">
          👥 Ibyakozwe n'abakozi bose
          <span class="text-muted small">
            ({{ request('start_date') }} - {{ request('end_date', now()->toDateString()) }})
          </span>
        </h5>
        <div class="dropdown">
          <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown">
            <i class="bi bi-download"></i> Export
          </button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="#" id="exportExcel"><i class="bi bi-file-earmark-excel"></i> Excel</a></li>
            <li><a class="dropdown-item" href="#" id="exportPDF"><i class="bi bi-file-earmark-pdf"></i> PDF</a></li>
          </ul>
        </div>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover datatable">
          <thead class="table-light">
            <tr>
              <th>Umukozi</th>
              @foreach($categories as $cat)
                <th class="text-center" colspan="2">{{ $cat->name }}</th>
              @endforeach
              <th>TOTAL</th>
              <th>Actions</th>
            </tr>
            <tr>
              <th></th>
              @foreach($categories as $cat)
                <th class="text-center">Qty</th>
                <th class="text-center">Amount</th>
              @endforeach
              <th class="text-center">Rwf</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse($summaryPerEmployee as $employeeName => $records)
            <tr>
              <td class="fw-bold">{{ $employeeName }}</td>
              @php
                $catMap = $records->keyBy('transport_category_id');
                $rowTotal = 0;
              @endphp
              @foreach($categories as $cat)
                @php
                  $r = $catMap->get($cat->id);
                  $qty = $r ? $r->total_qty : 0;
                  $price = $r ? $r->total_price : 0;
                  $rowTotal += $price;
                @endphp
                <td class="text-end">{{ number_format($qty) }}</td>
                <td class="text-end">{{ number_format($price) }}</td>
              @endforeach
              <td class="text-end fw-bold">{{ number_format($rowTotal) }}</td>
              <td class="text-center">
                @if($records->first()->employee_id ?? false)
                  <button class="btn btn-sm btn-outline-primary view-details" 
                          data-employee-id="{{ $records->first()->employee_id }}"
                          data-start-date="{{ request('start_date') }}"
                          data-end-date="{{ request('end_date', now()->toDateString()) }}">
                    <i class="bi bi-eye"></i> Details
                  </button>
                @endif
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="{{ 2 * count($categories) + 3 }}" class="text-center py-4 text-muted">
                <i class="bi bi-exclamation-circle"></i> No records found for selected filters
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

@include('theme.footer')

@push('scripts')
<script>
$(document).ready(function() {
  // Initialize Select2 for employee dropdown
  $('.select2').select2({
    placeholder: "Select employee",
    allowClear: true
  });

  // Initialize DataTable
  $('.datatable').DataTable({
    dom: '<"top"f>rt<"bottom"lip><"clear">',
    responsive: true,
    pageLength: 10,
    buttons: [
      'copy', 'csv', 'excel', 'pdf', 'print'
    ]
  });

  // Date range validation
  $('#endDate').change(function() {
    if ($('#startDate').val() > $(this).val()) {
      alert('End date cannot be before start date');
      $(this).val($('#startDate').val());
    }
  });

  // Print button
  $('#printBtn').click(function() {
    window.print();
  });

  // Export buttons
  $('#exportExcel').click(function(e) {
    e.preventDefault();
    $('.datatable').DataTable().button('.buttons-excel').trigger();
  });

  $('#exportPDF').click(function(e) {
    e.preventDefault();
    $('.datatable').DataTable().button('.buttons-pdf').trigger();
  });

  // View details modal
  $('.view-details').click(function() {
    const employeeId = $(this).data('employee-id');
    const startDate = $(this).data('start-date');
    const endDate = $(this).data('end-date');
    
    // Load details via AJAX or redirect
    window.location.href = `/transport-records/${employeeId}/details?start_date=${startDate}&end_date=${endDate}`;
  });

  // Auto-submit form when dates change
  $('#startDate, #endDate').change(function() {
    if ($('#startDate').val() && $('#endDate').val()) {
      $('#filterForm').submit();
    }
  });
});
</script>

<style>
  .card-icon {
    font-size: 1.5rem;
  }
  .info-card h6 {
    font-size: 1.1rem;
    font-weight: 600;
  }
  .table th {
    white-space: nowrap;
    vertical-align: middle;
  }
  @media print {
    .card-header, .pagetitle, .info-card, .dropdown {
      display: none !important;
    }
    .table {
      font-size: 0.8rem;
    }
  }
  @media (max-width: 768px) {
    .table-responsive {
      border: none;
    }
    .table thead {
      display: none;
    }
    .table tr {
      display: block;
      margin-bottom: 1rem;
      border: 1px solid #dee2e6;
    }
    .table td {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border: none;
      padding: 0.5rem;
    }
    .table td:before {
      content: attr(data-label);
      font-weight: bold;
      margin-right: 1rem;
    }
  }
</style>
@endpush