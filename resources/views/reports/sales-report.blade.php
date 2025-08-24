@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
      <h1><i class="bi bi-bar-chart-line-fill"></i> Sales Report Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
          <li class="breadcrumb-item active">Sales Report</li>
        </ol>
      </nav>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-secondary" id="printReport">
        <i class="bi bi-printer-fill"></i> Print
      </button>
      <a href="{{ route('sales.report.pdf', request()->query()) }}" class="btn btn-danger">
        <i class="bi bi-file-earmark-pdf-fill"></i> Export PDF
      </a>
    </div>
  </div>

  @include('theme.success')

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-funnel"></i> Filter Report</h5>
            
            <form method="GET" class="row g-3">
              <div class="col-md-3">
                <label for="from_date" class="form-label">From Date</label>
                <input type="date" class="form-control datepicker" name="from_date" value="{{ request('from_date') }}" id="from_date">
              </div>
              <div class="col-md-3">
                <label for="to_date" class="form-label">To Date</label>
                <input type="date" class="form-control datepicker" name="to_date" value="{{ request('to_date') }}" id="to_date">
              </div>
              <div class="col-md-3">
                <label for="customer_id" class="form-label">Customer</label>
                <select class="form-select select2" name="customer_id" id="customer_id">
                  <option value="">-- All Customers --</option>
                  @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                      {{ $customer->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label for="employee_id" class="form-label">Employee</label>
                <select class="form-select select2" name="employee_id" id="employee_id">
                  <option value="">-- All Employees --</option>
                  @foreach($employees as $employee)
                    <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                      {{ $employee->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-12 text-end">
                <button class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Apply Filters</button>
                <a href="{{ route('sales.report') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-box-seam"></i> Total Quantity</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 60px; height: 60px;">
                <i class="bi bi-cart-check" style="font-size: 1.5rem;"></i>
              </div>
              <div class="ps-3">
                <h2>{{ $summary['total_quantity'] }}</h2>
                <span class="text-muted small pt-2">Items sold</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title"><i class="bi bi-currency-exchange"></i> Total Sales</h5>
            <div class="d-flex align-items-center">
              <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success" style="width: 60px; height: 60px;">
                <i class="bi bi-cash-stack" style="font-size: 1.5rem;"></i>
              </div>
              <div class="ps-3">
                <h2>{{ number_format($summary['total_sales'], 2) }} RWF</h2>
                <span class="text-muted small pt-2">Total revenue</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="card-title"><i class="bi bi-receipt"></i> Sales Details</h5>
              <div class="dropdown">
                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="exportDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                  <i class="bi bi-download"></i> Export
                </button>
                <ul class="dropdown-menu" aria-labelledby="exportDropdown">
                  <li><a class="dropdown-item" href="#" id="exportCSV"><i class="bi bi-file-earmark-excel"></i> CSV</a></li>
                  <li><a class="dropdown-item" href="{{ route('sales.report.pdf', request()->query()) }}"><i class="bi bi-file-earmark-pdf"></i> PDF</a></li>
                </ul>
              </div>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered table-hover datatable">
                <thead class="table-primary">
                  <tr>
                    <th>#</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Employee</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                    <th>Payment</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($sales as $index => $sale)
                    <tr>
                      <td>{{ $index + 1 }}</td>
                      <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('M d, Y') }}</td>
                      <td>{{ $sale->customer->name }}</td>
                      <td>{{ $sale->employee->name }}</td>
                      <td>{{ $sale->quantity }}</td>
                      <td>{{ number_format($sale->unit_price, 2) }} RWF</td>
                      <td><strong>{{ number_format($sale->total_price, 2) }} RWF</strong></td>
                      <td>
                        <span class="badge bg-{{ $sale->payment_method === 'cash' ? 'success' : ($sale->payment_method === 'credit' ? 'warning' : 'info') }}">
                          {{ ucfirst($sale->payment_method) }}
                        </span>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="8" class="text-center text-muted py-4">No sales records found for the selected criteria</td>
                    </tr>
                  @endforelse
                </tbody>
                @if(count($sales) > 0)
                <tfoot class="table-light">
                  <tr>
                    <th colspan="4" class="text-end">Totals:</th>
                    <th>{{ $summary['total_quantity'] }}</th>
                    <th></th>
                    <th>{{ number_format($summary['total_sales'], 2) }} RWF</th>
                    <th></th>
                  </tr>
                </tfoot>
                @endif
              </table>
            </div>

            @if($sales instanceof \Illuminate\Pagination\LengthAwarePaginator && $sales->hasPages())
            <div class="mt-3">
              {{ $sales->appends(request()->query())->links() }}
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

@push('scripts')
<script>
  $(document).ready(function() {
    // Initialize datepicker
    $('.datepicker').datepicker({
      format: 'yyyy-mm-dd',
      autoclose: true
    });

    // Initialize select2
    $('.select2').select2({
      theme: 'bootstrap-5',
      placeholder: $(this).data('placeholder'),
      allowClear: true
    });

    // Print functionality
    $('#printReport').click(function() {
      window.print();
    });

    // Export CSV functionality
    $('#exportCSV').click(function(e) {
      e.preventDefault();
      // Get current filters
      let params = new URLSearchParams(window.location.search);
      window.location.href = "{{ route('sales.report.csv') }}?" + params.toString();
    });

    // DataTable initialization
    $('.datatable').DataTable({
      dom: '<"top"f>rt<"bottom"lip><"clear">',
      responsive: true,
      pageLength: 25,
      order: [[1, 'desc']],
      language: {
        search: "_INPUT_",
        searchPlaceholder: "Search sales...",
      }
    });
  });
</script>
@endpush

@include('theme.footer')