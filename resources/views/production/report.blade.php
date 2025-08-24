<!DOCTYPE html>
<html lang="en">

@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1><i class="bi bi-clipboard-data"></i> Production Report</h1>
    <button class="btn btn-outline-success" onclick="exportToExcel()">
      <i class="bi bi-file-earmark-excel-fill"></i> Export Excel
    </button>
  </div>

  <section class="section">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">

        {{-- Alert for Filters --}}
        @if(request()->filled('employee_id') || request()->filled('from_date') || request()->filled('to_date'))
          <div class="alert alert-primary small d-flex align-items-center gap-2">
            <i class="bi bi-funnel-fill text-primary fs-5"></i>
            <div>
              <strong>Filter Applied: </strong>
              @if(request()->filled('employee_id'))
                <span class="badge bg-info text-dark">
                  Employee: {{ $employees->firstWhere('id', request('employee_id'))?->name ?? 'Unknown' }}
                </span>
              @endif
              @if(request()->filled('from_date') && request()->filled('to_date'))
                <span class="badge bg-secondary"> {{ request('from_date') }} – {{ request('to_date') }}</span>
              @elseif(request()->filled('from_date'))
                <span class="badge bg-secondary"> From: {{ request('from_date') }}</span>
              @elseif(request()->filled('to_date'))
                <span class="badge bg-secondary"> To: {{ request('to_date') }}</span>
              @endif
            </div>
          </div>
        @endif

        {{-- Filter Form --}}
        <form method="GET" class="row g-3 align-items-end mb-4 border rounded p-3 bg-light">
          <div class="col-md-4">
            <label class="form-label">Employee</label>
            <select name="employee_id" class="form-select">
              <option value="">-- All Employees --</option>
              @foreach($employees as $emp)
                <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
                  {{ $emp->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">From</label>
            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
          </div>
          <div class="col-md-3">
            <label class="form-label">To</label>
            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
          </div>
          <div class="col-md-2 d-flex gap-2">
            <button class="btn btn-primary w-100"><i class="bi bi-filter"></i> Filter</button>
            <a href="{{ route('productions.report') }}" class="btn btn-outline-secondary">
              <i class="bi bi-x-circle"></i>
            </a>
          </div>
        </form>

        {{-- Chart --}}
        <div class="mb-4 border rounded p-3 bg-white" style="height: 400px;">
          <h5 class="mb-3 text-primary"><i class="bi bi-bar-chart-line"></i> Bricks vs Amount</h5>
          <div style="height: 100%;">
            <canvas id="employeeChart"></canvas>
          </div>
        </div>

        {{-- Totals --}}
        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <div class="card shadow-sm border-start border-4 border-primary h-100">
              <div class="card-body d-flex align-items-center">
                <div class="me-3">
                  <i class="bi bi-box-seam text-primary fs-1"></i>
                </div>
                <div>
                  <h6 class="text-muted mb-1">Total Bricks</h6>
                  <h3 class="text-primary mb-0">{{ number_format($totalQuantity) }}</h3>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="card shadow-sm border-start border-4 border-success h-100">
              <div class="card-body d-flex align-items-center">
                <div class="me-3">
                  <i class="bi bi-cash-stack text-success fs-1"></i>
                </div>
                <div>
                  <h6 class="text-muted mb-1">Total Value</h6>
                  <h3 class="text-success mb-0">{{ number_format($totalValue) }} Frw</h3>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- Summary Grouped by Employee --}}
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle" id="summaryTable">
            <thead class="table-light text-center">
              <tr>
                <th>#</th>
                <th>👷 Employee</th>
                <th class="text-end">🧱 Quantity</th>
                <th class="text-end">💰 Amount (Frw)</th>
                <th class="text-center">Details</th>
              </tr>
            </thead>
            <tbody>
              @php $i = 1; @endphp
              @forelse($productions->groupBy('employee.name') as $empName => $records)
                @php
                  $totalQty = $records->sum('quantity');
                  $totalAmt = $records->sum(fn($r) => $r->quantity * $r->unit_price);
                @endphp
                <tr>
                  <td class="text-center">{{ $i++ }}</td>
                  <td>{{ $empName }}</td>
                  <td class="text-end">{{ number_format($totalQty) }}</td>
                  <td class="text-end text-success">{{ number_format($totalAmt) }}</td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                            data-bs-target="#detailsModal_{{ Str::slug($empName, '_') }}">
                      <i class="bi bi-eye"></i> Show
                    </button>
                  </td>
                </tr>

                {{-- Details Modal --}}
              {{-- Details Modal --}}
<div class="modal fade" id="detailsModal_{{ Str::slug($empName, '_') }}" tabindex="-1"
    aria-labelledby="detailsLabel_{{ Str::slug($empName, '_') }}" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title">
          <i class="bi bi-person-lines-fill"></i> {{ $empName }} — Detailed Records
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body pb-0">
        <div class="row g-4">
          @foreach($records->sortByDesc('production_date') as $rec)
          <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100 border-start border-4 border-primary">
              <div class="card-body">
                <h6 class="text-muted small mb-2">
                  <i class="bi bi-calendar2-date"></i>
                  {{ \Carbon\Carbon::parse($rec->production_date)->format('M d, Y') }}
                </h6>
                <div class="mb-1 d-flex justify-content-between">
                  <span class="fw-bold text-primary">🧱 Quantity</span>
                  <span>{{ number_format($rec->quantity) }}</span>
                </div>
                <div class="mb-1 d-flex justify-content-between">
                  <span class="fw-bold text-success">💵 Unit Price</span>
                  <span>{{ number_format($rec->unit_price) }} Frw</span>
                </div>
                <div class="d-flex justify-content-between">
                  <span class="fw-bold text-dark">💰 Total</span>
                  <span class="fw-bold text-primary">
                    {{ number_format($rec->quantity * $rec->unit_price) }} Frw
                  </span>
                </div>
              </div>
            </div>
          </div>
          @endforeach
        </div>

        {{-- ✅ Totals Section --}}
        @php
          $grandTotal = $records->sum(fn($r) => $r->quantity * $r->unit_price);
          $totalBricks = $records->sum('quantity');
        @endphp

        <div class="mt-4 pt-3 border-top border-2">
          <div class="alert alert-light d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
            <div>
              <span class="fw-bold fs-6"><i class="bi bi-bricks"></i> Total Bricks:</span>
              <span class="text-primary fw-semibold fs-5">{{ number_format($totalBricks) }}</span>
            </div>
            <div>
              <span class="fw-bold fs-6"><i class="bi bi-cash-coin"></i> Total Amount:</span>
              <span class="text-success fw-bold fs-5">{{ number_format($grandTotal) }} Frw</span>
            </div>
          </div>
        </div>
        {{-- END totals --}}
      </div>

      <div class="modal-footer bg-light">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
              @empty
                <tr><td colspan="5" class="text-center">No data available.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </section>
</main>
@include('theme.footer')

{{-- Chart + Excel Export --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  const labels = {!! json_encode($chartLabels ?? []) !!};
  const bricks = {!! json_encode($chartValues ?? []) !!};
  const amounts = {!! json_encode($chartAmounts ?? []) !!};

  const ctx = document.getElementById('employeeChart').getContext('2d');
  if (labels.length) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Bricks',
            data: bricks,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            yAxisID: 'y1',
            borderRadius: 6
          },
          {
            label: 'Amount (Frw)',
            data: amounts,
            backgroundColor: 'rgba(75, 192, 192, 0.7)',
            yAxisID: 'y2',
            borderRadius: 6
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y1: {
            type: 'linear',
            position: 'left',
            beginAtZero: true,
            title: { display: true, text: 'Bricks' }
          },
          y2: {
            type: 'linear',
            position: 'right',
            beginAtZero: true,
            grid: { drawOnChartArea: false },
            title: { display: true, text: 'Amount' }
          }
        },
        plugins: {
          tooltip: {
            callbacks: {
              label: function (ctx) {
                const val = ctx.raw;
                return ctx.dataset.label === "Amount (Frw)"
                  ? `${ctx.dataset.label}: ${val.toLocaleString()} Frw`
                  : `${ctx.dataset.label}: ${val} Bricks`;
              }
            }
          },
          legend: { position: 'bottom' }
        }
      }
    });
  }

});

function exportToExcel() {
  const table = document.getElementById("summaryTable");
  const wb = XLSX.utils.table_to_book(table, { sheet: "Production Summary" });
  XLSX.writeFile(wb, `production_summary_{{ date('Ymd_His') }}.xlsx`);
}
</script>

