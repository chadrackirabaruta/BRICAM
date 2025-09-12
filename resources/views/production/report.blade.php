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
                  $empSlug = Str::slug($empName, '_');
                @endphp
                <tr>
                  <td class="text-center">{{ $i++ }}</td>
                  <td>{{ $empName }}</td>
                  <td class="text-end">{{ number_format($totalQty) }}</td>
                  <td class="text-end text-success">{{ number_format($totalAmt) }}</td>
                  <td class="text-center">
                    <button class="btn btn-sm btn-outline-primary show-details-btn"
                            data-employee="{{ $empName }}" 
                            data-records="{{ json_encode($records->values()) }}"
                            data-total-qty="{{ $totalQty }}"
                            data-total-amt="{{ $totalAmt }}">
                      <i class="bi bi-eye"></i> Show Details
                    </button>
                  </td>
                </tr>
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

{{-- Custom Overlay Modal (No Bootstrap Modal) --}}
<div id="detailsOverlay" class="details-overlay">
  <div class="details-container">
    <div class="details-header">
      <h4 id="modalTitle">
        <i class="bi bi-person-lines-fill text-primary"></i>
        <span id="employeeName">Employee Name</span> — Detailed Records
      </h4>
      <button type="button" class="close-btn" id="closeModal">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    
    <div class="details-body">
      <div id="recordsContainer" class="records-grid">
        <!-- Records will be populated here -->
      </div>
      
      <div class="totals-section">
        <div class="totals-row">
          <div class="total-item">
            <i class="bi bi-bricks text-primary"></i>
            <span class="label">Total Bricks:</span>
            <span id="totalBricks" class="value text-primary">0</span>
          </div>
          <div class="total-item">
            <i class="bi bi-cash-coin text-success"></i>
            <span class="label">Total Amount:</span>
            <span id="totalAmount" class="value text-success">0 Frw</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@include('theme.footer')

{{-- Chart + Excel Export + Custom Modal --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
  // Initialize custom modal functionality
  initCustomModal();

  // Chart initialization
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

// Custom Modal Implementation (No Bootstrap)
function initCustomModal() {
  const overlay = document.getElementById('detailsOverlay');
  const closeBtn = document.getElementById('closeModal');
  const showDetailsBtns = document.querySelectorAll('.show-details-btn');

  // Show modal function
  function showModal(employeeName, records, totalQty, totalAmt) {
    // Update modal title
    document.getElementById('employeeName').textContent = employeeName;
    
    // Update totals
    document.getElementById('totalBricks').textContent = Number(totalQty).toLocaleString();
    document.getElementById('totalAmount').textContent = Number(totalAmt).toLocaleString() + ' Frw';
    
    // Generate records HTML
    const recordsContainer = document.getElementById('recordsContainer');
    let recordsHTML = '';
    
    JSON.parse(records).forEach(record => {
      const date = new Date(record.production_date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      });
      const total = record.quantity * record.unit_price;
      
      recordsHTML += `
        <div class="record-card">
          <div class="record-date">
            <i class="bi bi-calendar2-date"></i>
            ${date}
          </div>
          <div class="record-details">
            <div class="detail-row">
              <span class="label">🧱 Quantity</span>
              <span class="value">${Number(record.quantity).toLocaleString()}</span>
            </div>
            <div class="detail-row">
              <span class="label">💵 Unit Price</span>
              <span class="value">${Number(record.unit_price).toLocaleString()} Frw</span>
            </div>
            <div class="detail-row total-row">
              <span class="label">💰 Total</span>
              <span class="value">${Number(total).toLocaleString()} Frw</span>
            </div>
          </div>
        </div>
      `;
    });
    
    recordsContainer.innerHTML = recordsHTML;
    
    // Show overlay
    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  // Hide modal function
  function hideModal() {
    overlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  // Event listeners
  showDetailsBtns.forEach(btn => {
    btn.addEventListener('click', function() {
      const employeeName = this.dataset.employee;
      const records = this.dataset.records;
      const totalQty = this.dataset.totalQty;
      const totalAmt = this.dataset.totalAmt;
      
      showModal(employeeName, records, totalQty, totalAmt);
    });
  });

  // Close modal events
  closeBtn.addEventListener('click', hideModal);
  
  overlay.addEventListener('click', function(e) {
    if (e.target === overlay) {
      hideModal();
    }
  });
  
  // ESC key to close
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && overlay.classList.contains('active')) {
      hideModal();
    }
  });
}

// Excel Export
function exportToExcel() {
  const table = document.getElementById("summaryTable");
  const wb = XLSX.utils.table_to_book(table, { sheet: "Production Summary" });
  XLSX.writeFile(wb, `production_summary_{{ date('Ymd_His') }}.xlsx`);
}
</script>

