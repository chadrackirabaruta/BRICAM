@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center mb-4">
    <div>
      <h1><i class="bi bi-boxes text-primary"></i> Stock Movement Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="#"><i class="bi bi-house-door"></i></a></li>
          <li class="breadcrumb-item">Reports</li>
          <li class="breadcrumb-item active">Stock Summary</li>
        </ol>
      </nav>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-primary" id="refreshBtn" data-bs-toggle="tooltip" title="Refresh Data">
        <i class="bi bi-arrow-clockwise"></i>
      </button>
      <button class="btn btn-outline-secondary" id="viewToggle" data-bs-toggle="tooltip" title="Toggle View">
        <i class="bi bi-grid-3x3-gap"></i>
      </button>
    </div>
  </div>

  @include('theme.success')

  <!-- ENHANCED FILTER FORM -->
  <section class="section mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-gradient-primary text-white">
            <h6 class="mb-0"><i class="bi bi-funnel-fill me-2"></i>Filter & Controls</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.stock-summary') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold"><i class="bi bi-calendar-range me-1"></i>Period</label>
                    <select name="filter" id="filterSelect" class="form-select form-select-lg">
                        <option value="day" {{ $filter === 'day' ? 'selected' : '' }}>
                            <i class="bi bi-sun"></i> Today
                        </option>
                        <option value="week" {{ $filter === 'week' ? 'selected' : '' }}>
                            <i class="bi bi-calendar-week"></i> This Week
                        </option>
                        <option value="month" {{ $filter === 'month' ? 'selected' : '' }}>
                            <i class="bi bi-calendar-month"></i> This Month
                        </option>
                        <option value="year" {{ $filter === 'year' ? 'selected' : '' }}>
                            <i class="bi bi-calendar"></i> This Year
                        </option>
                        <option value="between" {{ $filter === 'between' ? 'selected' : '' }}>
                            <i class="bi bi-calendar-range"></i> Between Dates
                        </option>
                    </select>
                </div>

                <!-- Date Range Picker (hidden by default) -->
                <div class="col-md-4" id="dateRangeContainer" style="{{ $filter !== 'between' ? 'display: none;' : '' }}">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-lg" 
                                   value="{{ $start_date ?? '' }}" max="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-lg" 
                                   value="{{ $end_date ?? '' }}" max="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                </div>

                <div class="col-md-{{ $filter === 'between' ? '2' : '3' }}">
                    <label class="form-label fw-bold"><i class="bi bi-tags me-1"></i>Stock Type</label>
                    <select name="stock_type_id" class="form-select form-select-lg">
                        <option value="">All Types</option>
                        @foreach($stockTypes as $type)
                            <option value="{{ $type->id }}" {{ $selectedType == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-{{ $filter === 'between' ? '2' : '3' }}">
                    <label class="form-label fw-bold text-white d-block">.</label>
                    <div class="btn-group w-100" role="group">
                        <a href="{{ route('reports.stock-summary.export', request()->query()) }}" 
                           class="btn btn-success btn-lg">
                            <i class="bi bi-download me-1"></i> CSV
                        </a>
                        <button type="button" class="btn btn-info btn-lg" id="printBtn">
                            <i class="bi bi-printer me-1"></i> Print
                        </button>
                    </div>
                </div>

                <div class="col-md-{{ $filter === 'between' ? '1' : '3' }}">
                    <label class="form-label fw-bold text-white d-block">.</label>
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-filter-circle me-1"></i> Apply
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>


  <!-- INTERACTIVE SUMMARY CARDS -->
  <section class="section">
    <div class="row" id="stockCards">
      @forelse($logs as $index => $log)
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="card stock-card shadow-hover border-0 h-100" data-aos="fade-up" data-aos-delay="{{ $index * 100 }}">
            <div class="card-body position-relative">
              <!-- Status Indicator -->
              <div class="position-absolute top-0 end-0 p-2">
                <span class="badge {{ $log['net'] >= 0 ? 'bg-success' : 'bg-danger' }} pulse">
                  {{ $log['net'] >= 0 ? 'Positive' : 'Negative' }}
                </span>
              </div>

              <!-- Stock Type Header -->
              <div class="d-flex align-items-center mb-3">
                <div class="icon-wrapper me-3">
                  <i class="bi bi-box-seam fs-1 text-primary"></i>
                </div>
                <div>
                  <h5 class="card-title mb-1">{{ $log['stock_type'] }}</h5>
                  <small class="text-muted">Stock Movement</small>
                </div>
              </div>

              <!-- Interactive Counters -->
              <div class="stock-counters">
                <!-- Stock In Counter -->
                <div class="counter-item mb-3 p-3 rounded bg-success-subtle border-start border-success border-4">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-arrow-up-circle-fill text-success fs-3 me-2"></i>
                      <div>
                        <small class="text-muted d-block">Stock In</small>
                        <span class="counter-value fs-4 fw-bold text-success" data-target="{{ $log['in'] }}">0</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Stock Out Counter -->
                <div class="counter-item mb-3 p-3 rounded bg-danger-subtle border-start border-danger border-4">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-arrow-down-circle-fill text-danger fs-3 me-2"></i>
                      <div>
                        <small class="text-muted d-block">Stock Out</small>
                        <span class="counter-value fs-4 fw-bold text-danger" data-target="{{ $log['out'] }}">0</span>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Net Movement -->
                <div class="counter-item p-3 rounded {{ $log['net'] >= 0 ? 'bg-primary-subtle border-start border-primary' : 'bg-warning-subtle border-start border-warning' }} border-4">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-{{ $log['net'] >= 0 ? 'trending-up' : 'trending-down' }} fs-3 me-2 {{ $log['net'] >= 0 ? 'text-primary' : 'text-warning' }}"></i>
                      <div>
                        <small class="text-muted d-block">Net Movement</small>
                        <span class="counter-value fs-4 fw-bold {{ $log['net'] >= 0 ? 'text-primary' : 'text-warning' }}" data-target="{{ $log['net'] }}">0</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Progress Bar -->
              <div class="mt-3">
                <div class="d-flex justify-content-between small text-muted mb-1">
                  <span>Movement Ratio</span>
                  <span>{{ $log['in'] > 0 ? round(($log['out'] / $log['in']) * 100, 1) : 0 }}%</span>
                </div>
                <div class="progress" style="height: 6px;">
                  <div class="progress-bar bg-gradient-primary" role="progressbar" 
                       style="width: {{ $log['in'] > 0 ? min(($log['out'] / $log['in']) * 100, 100) : 0 }}%"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      @empty
        <div class="col-12">
          <div class="text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h4 class="text-muted mt-3">No Stock Data Found</h4>
            <p class="text-muted">No stock movements recorded for the selected period.</p>
            <button class="btn btn-primary" onclick="location.reload()">
              <i class="bi bi-arrow-clockwise me-2"></i>Refresh Data
            </button>
          </div>
        </div>
      @endforelse
    </div>
  </section>

  <!-- ENHANCED CORRECTION LOGS -->
  @if(isset($corrections) && $corrections->count())
  <section class="section mt-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-gradient-warning text-dark">
        <div class="d-flex justify-content-between align-items-center">
          <h6 class="mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>Recent Stock Corrections</h6>
          <button class="btn btn-sm btn-outline-dark" data-bs-toggle="collapse" data-bs-target="#correctionsCollapse">
            <i class="bi bi-chevron-down"></i>
          </button>
        </div>
      </div>
      <div class="collapse show" id="correctionsCollapse">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="table-light">
                <tr>
                  <th><i class="bi bi-calendar3 me-1"></i>Date</th>
                  <th><i class="bi bi-box me-1"></i>Stock Type</th>
                  <th><i class="bi bi-123 me-1"></i>Quantity</th>
                  <th><i class="bi bi-person me-1"></i>Corrected By</th>
                </tr>
              </thead>
              <tbody>
                @foreach($corrections as $log)
                  <tr>
                    <td>
                      <span class="badge bg-light text-dark">{{ $log->stock_date }}</span>
                    </td>
                    <td>
                      <strong>{{ $log->stockType->name ?? 'N/A' }}</strong>
                    </td>
                    <td>
                      <span class="badge bg-info">{{ number_format($log->quantity) }}</span>
                    </td>
                    <td>
                      @if($log->employee)
                        <div class="d-flex align-items-center">
                          <i class="bi bi-person-circle me-2"></i>
                          {{ $log->employee->name }}
                        </div>
                      @else
                        <span class="text-muted">System</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
  @endif

  <!-- ENHANCED CHARTS -->
  @if(count($logs))
  <section class="section mt-5">
    <div class="row">
      <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
              <h6 class="mb-0"><i class="bi bi-bar-chart-fill me-2"></i>Stock Movement Analysis</h6>
              <div class="btn-group btn-group-sm" role="group">
                <input type="radio" class="btn-check" name="chartType" id="barChart" autocomplete="off" checked>
                <label class="btn btn-outline-primary" for="barChart">Bar</label>
                <input type="radio" class="btn-check" name="chartType" id="lineChart" autocomplete="off">
                <label class="btn btn-outline-primary" for="lineChart">Line</label>
              </div>
            </div>
          </div>
          <div class="card-body">
            <div class="chart-container" style="position: relative; height: 400px;">
              <canvas id="mainChart"></canvas>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-pie-chart-fill me-2"></i>Distribution</h6>
          </div>
          <div class="card-body">
            <div class="chart-container" style="position: relative; height: 400px;">
              <canvas id="pieChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- QUICK STATS ROW -->
  <section class="section mt-4">
    <div class="row">
      <div class="col-md-4 mb-3">
        <div class="card bg-primary text-white border-0">
          <div class="card-body text-center">
            <i class="bi bi-currency-dollar display-4 mb-2"></i>
            <h4 id="totalSold">{{ $logs->isNotEmpty() ? number_format($logs->last()['out']) : '0' }}</h4>
            <small>Total Sold</small>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card bg-warning text-white border-0">
          <div class="card-body text-center">
            <i class="bi bi-arrow-return-left display-4 mb-2"></i>
            <h4 id="totalReturned">{{ isset($corrections) ? number_format($corrections->sum('quantity')) : '0' }}</h4>
            <small>Returned/Cancelled</small>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-3">
        <div class="card bg-info text-white border-0">
          <div class="card-body text-center">
            <i class="bi bi-boxes display-4 mb-2"></i>
            <h4>{{ count($logs) }}</h4>
            <small>Stock Types</small>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CURRENT AVAILABLE STOCK BY TYPE -->
  @if(count($logs))
  <section class="section mt-4">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-gradient-primary text-white">
        <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Current Available Stock by Type</h6>
      </div>
      <div class="card-body">
        <div class="row">
   <!-- Remaining Production Card -->
<!-- Remaining Production Card -->
<div class="col-lg-3 col-md-4 col-sm-6 mb-3">
  <div class="card border-0 bg-light h-100">
    <div class="card-body text-center">
      <div class="mb-2">
        <i class="bi bi-gear-fill text-secondary fs-2"></i>
      </div>
      <h6 class="card-title">Remaining Production</h6>
      <h4 class="mb-1 text-secondary">
        {{ number_format($remainingProduction ?? 0) }}
      </h4>
      <small class="text-muted">Still in stock</small>
    </div>
  </div>
</div>


          
          <!-- Individual Stock Types -->
          @foreach($logs as $log)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
              <div class="card border-0 bg-light h-100">
                <div class="card-body text-center">
                  <div class="mb-2">
                    <i class="bi bi-box text-primary fs-2"></i>
                  </div>
                  <h6 class="card-title text-truncate" title="{{ $log['stock_type'] }}">{{ $log['stock_type'] }}</h6>
                  <h4 class="mb-1 {{ $log['net'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($log['net']) }}
                  </h4>
                  <small class="text-muted">Available Units</small>
                  @if($log['net'] < 0)
                    <div class="mt-2">
                      <span class="badge bg-warning">Low Stock</span>
                    </div>
                  @endif
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </section>
  @endif
  @endif

</main>

@include('theme.footer')

<!-- Modal for Details -->
<div class="modal fade" id="detailsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-info-circle me-2"></i>Stock Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="modalContent">
        <!-- Dynamic content loaded here -->
      </div>
    </div>
  </div>
</div>
<!-- Enhanced Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" />
<style>
.bg-gradient-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-warning {
  background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.stock-card {
  transition: all 0.3s ease;
  border-left: 4px solid transparent;
}

.stock-card:hover {
  transform: translateY(-5px);
  border-left-color: var(--bs-primary);
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

.shadow-hover {
  transition: box-shadow 0.3s ease;
}

.counter-item {
  transition: all 0.3s ease;
}

.counter-item:hover {
  transform: scale(1.02);
}

.pulse {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.icon-wrapper {
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

.animated {
  animation-duration: 0.6s;
  animation-fill-mode: both;
}

.fadeIn {
  animation-name: fadeIn;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Chart Container Styles */
.chart-container {
  position: relative;
  width: 100%;
}

.chart-container canvas {
  max-height: 400px !important;
}

/* Ensure proper chart responsiveness */
@media (max-width: 768px) {
  .chart-container {
    height: 300px !important;
  }
  
  .chart-container canvas {
    max-height: 300px !important;
  }
}

/* Loading overlay for charts */
.chart-loading {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 10;
}
</style>

<script>
$(document).ready(function() {
  // Initialize AOS
  AOS.init({
    duration: 800,
    once: true
  });

  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Counter animation
  animateCounters();

  // Date range picker
  $('#daterange').daterangepicker({
    opens: 'right',
    autoUpdateInput: true,
    locale: {
      format: 'YYYY-MM-DD',
      cancelLabel: 'Clear'
    }
  });

  // Chart initialization
  initializeCharts();
});

// Counter Animation
function animateCounters() {
  $('.counter-value').each(function() {
    const $this = $(this);
    const target = parseInt($this.data('target'));
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;
    
    const timer = setInterval(function() {
      current += step;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      $this.text(Math.floor(current).toLocaleString());
    }, 16);
  });
}

// Chart Data Setup
const labels = @json($logs->pluck('stock_type'));
const inData = @json($logs->pluck('in'));
const outData = @json($logs->pluck('out'));
const netData = @json($logs->pluck('net'));

let mainChart, pieChart;

function initializeCharts() {
  if (labels.length) {
    // Main Chart
    const mainCtx = document.getElementById('mainChart').getContext('2d');
    mainChart = new Chart(mainCtx, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          {
            label: 'Stock In',
            data: inData,
            backgroundColor: 'rgba(25, 135, 84, 0.8)',
            borderColor: 'rgba(25, 135, 84, 1)',
            borderWidth: 2,
            borderRadius: 4
          },
          {
            label: 'Stock Out',
            data: outData,
            backgroundColor: 'rgba(220, 53, 69, 0.8)',
            borderColor: 'rgba(220, 53, 69, 1)',
            borderWidth: 2,
            borderRadius: 4
          },
          {
            label: 'Net Movement',
            data: netData,
            backgroundColor: 'rgba(13, 110, 253, 0.8)',
            borderColor: 'rgba(13, 110, 253, 1)',
            borderWidth: 2,
            borderRadius: 4
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            labels: {
              usePointStyle: true,
              padding: 20,
              font: {
                size: 12,
                weight: 'bold'
              }
            }
          },
          tooltip: {
            mode: 'index',
            intersect: false,
            backgroundColor: 'rgba(0,0,0,0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: 'rgba(255,255,255,0.1)',
            borderWidth: 1,
            cornerRadius: 8,
            displayColors: true,
            callbacks: {
              label: function(context) {
                return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
              }
            }
          }
        },
        scales: {
          y: { 
            beginAtZero: true,
            grid: {
              color: 'rgba(0,0,0,0.1)',
              drawBorder: false
            },
            ticks: {
              font: {
                size: 11
              },
              callback: function(value) {
                return value.toLocaleString();
              }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              font: {
                size: 11
              },
              maxRotation: 45
            }
          }
        },
        interaction: {
          mode: 'nearest',
          axis: 'x',
          intersect: false
        },
        elements: {
          bar: {
            borderSkipped: false,
          }
        }
      }
    });

    // Pie Chart
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    pieChart = new Chart(pieCtx, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data: netData.map(val => Math.abs(val)),
          backgroundColor: [
            '#FF6384',
            '#36A2EB', 
            '#FFCE56',
            '#4BC0C0',
            '#9966FF',
            '#FF9F40',
            '#FF6384',
            '#C9CBCF'
          ],
          borderWidth: 3,
          borderColor: '#fff',
          hoverBorderWidth: 4,
          hoverOffset: 10
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '50%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              usePointStyle: true,
              padding: 15,
              font: {
                size: 11
              }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0,0,0,0.8)',
            titleColor: '#fff',
            bodyColor: '#fff',
            borderColor: 'rgba(255,255,255,0.1)',
            borderWidth: 1,
            cornerRadius: 8,
            callbacks: {
              label: function(context) {
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = ((context.parsed / total) * 100).toFixed(1);
                return context.label + ': ' + context.parsed.toLocaleString() + ' (' + percentage + '%)';
              }
            }
          }
        },
        animation: {
          animateScale: true,
          animateRotate: true,
          duration: 2000
        }
      }
    });

    // Chart type toggle
    $('input[name="chartType"]').change(function() {
      const newType = $(this).attr('id') === 'barChart' ? 'bar' : 'line';
      
      // Update chart type and styling
      if (newType === 'line') {
        mainChart.config.type = 'line';
        mainChart.data.datasets.forEach(dataset => {
          dataset.fill = false;
          dataset.tension = 0.4;
          dataset.pointRadius = 6;
          dataset.pointHoverRadius = 8;
          dataset.pointBackgroundColor = dataset.borderColor;
          dataset.pointBorderColor = '#fff';
          dataset.pointBorderWidth = 2;
        });
      } else {
        mainChart.config.type = 'bar';
        mainChart.data.datasets.forEach(dataset => {
          delete dataset.fill;
          delete dataset.tension;
          delete dataset.pointRadius;
          delete dataset.pointHoverRadius;
          delete dataset.pointBackgroundColor;
          delete dataset.pointBorderColor;
          delete dataset.pointBorderWidth;
          dataset.borderRadius = 4;
        });
      }
      
      mainChart.update('active');
    });
  }
}

// Interactive Functions
function showDetails(type, stockType) {
  // Function removed as requested
}

function viewCorrection(id) {
  // Function removed as requested
}

// Button Events
$('#refreshBtn').click(function() {
  $(this).find('i').addClass('fa-spin');
  setTimeout(() => {
    location.reload();
  }, 1000);
});

$('#viewToggle').click(function() {
  $('#stockCards').toggleClass('row-cols-1 row-cols-md-2 row-cols-lg-3');
});

$('#printBtn').click(function() {
  window.print();
});

$('#analyticsBtn').click(function() {
  alert('Advanced analytics feature coming soon!');
});


  document.addEventListener('DOMContentLoaded', function() {
        const filterSelect = document.getElementById('filterSelect');
        const dateRangeContainer = document.getElementById('dateRangeContainer');

        // Toggle date range visibility
        filterSelect.addEventListener('change', function() {
            if (this.value === 'between') {
                dateRangeContainer.style.display = 'block';
            } else {
                dateRangeContainer.style.display = 'none';
            }
        });

        // Auto-submit when period or stock type changes (except for between dates)
        document.querySelectorAll('select[name="filter"], select[name="stock_type_id"]').forEach(select => {
            select.addEventListener('change', function() {
                if (filterSelect.value !== 'between') {
                    this.form.submit();
                }
            });
        });
    });
</script>



