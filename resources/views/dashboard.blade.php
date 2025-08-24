<!DOCTYPE html>
<html lang="en">
  @include('theme.head')

  <head>
    <!-- Chart.js and other dependencies -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

  </head>

  <body>
    @include('theme.header')
    @include('theme.sidebar')

    <main id="main" class="main">
      <div class="pagetitle">
        <h1><i class="bi bi-speedometer2 me-2"></i>Dashboard</h1>
        <nav>
          <ol class="breadcrumb">
            <li class="breadcrumb-item active">Dashboard</li>
          </ol>
        </nav>
      </div>

      <section class="section dashboard">
        <div class="row">
          <div class="col-lg-12">
            <div class="row">
      <!-- Users Card -->
<div class="col-xxl-4 col-md-6">
  <div class="card info-card users-card">
    <div class="card-body">
      <h5 class="card-title">System Users <span>| All Time</span></h5>
      <div class="d-flex align-items-center">
        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-secondary-light">
          <i class="bi bi-person-circle text-secondary"></i>
        </div>
        <div class="ps-3">
          <h6>{{ number_format(App\Models\User::count()) }}</h6>
          <span class="text-muted small pt-2 ps-1">Total accounts</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Employees Card -->
<div class="col-xxl-4 col-md-6">
  <div class="card info-card employees-card">
    <div class="card-body">
      <h5 class="card-title">Employees <span>| All Time</span></h5>
      <div class="d-flex align-items-center">
        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-warning-light">
          <i class="bi bi-person-badge text-warning"></i>
        </div>
        <div class="ps-3">
         <h6>{{ number_format(App\Models\Employee::active()->count()) }}</h6>
          <span class="text-muted small pt-2 ps-1">Team members</span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Customers Card -->
<div class="col-xxl-4 col-md-6">
  <div class="card info-card customers-card">
    <div class="card-body">
      <h5 class="card-title">Customers <span>| All Time</span></h5>
      <div class="d-flex align-items-center">
        <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-info-light">
          <i class="bi bi-people text-info"></i>
        </div>
        <div class="ps-3">
          <h6>{{ number_format(App\Models\Customer::active()->count()) }}</h6>
          <span class="text-muted small pt-2 ps-1">Total customers</span>
        </div>
      </div>
    </div>
  </div>
</div>

              <!-- Filter Controls -->
              <div class="col-12 mb-3">
                <div class="d-flex flex-wrap gap-2 align-items-end">
                  <div>
                    <label for="filter" class="form-label mb-1">Select Period</label>
                    <select id="filter" class="form-select form-select-sm"
                            onchange="window.location.href='{{ route('dashboard') }}?filter=' + this.value">
                      <option value="today" {{ ($filter ?? 'today') === 'today' ? 'selected' : '' }}>Today</option>
                      <option value="week" {{ ($filter ?? '') === 'week' ? 'selected' : '' }}>This Week</option>
                      <option value="month" {{ ($filter ?? '') === 'month' ? 'selected' : '' }}>This Month</option>
                      <option value="year" {{ ($filter ?? '') === 'year' ? 'selected' : '' }}>This Year</option>
                      <option value="all" {{ ($filter ?? '') === 'all' ? 'selected' : '' }}>All Time</option>
                    </select>
                  </div>
                  <div class="ms-auto">
                    <span class="text-muted small">Last updated: {{ $lastUpdated ?? now()->format('Y-m-d H:i') }}</span>
                  </div>
                </div>
              </div>

              <!-- Sales Card -->
              <div class="col-xxl-4 col-md-6">
                <div class="card info-card sales-card">
                  <div class="card-body">
                    <h5 class="card-title">Sales <span>| {{ $periodLabel ?? 'This Period' }}</span></h5>
                    <div class="d-flex align-items-center">
                      <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-primary-light">
                        <i class="bi bi-cart text-primary"></i>
                      </div>
                      <div class="ps-3">
                        <h6>{{ number_format($salesCount ?? 0) }}</h6>
                        @if(!empty($hasComparison))
                          <span class="{{ ($salesPercentChange ?? 0) >= 0 ? 'text-success' : 'text-danger' }} small pt-1 fw-bold">
                            {{ ($salesPercentChange ?? 0) >= 0 ? '+' : '' }}{{ $salesPercentChange ?? 0 }}%
                          </span>
                          <span class="text-muted small pt-2 ps-1">{{ $comparisonText ?? '' }}</span>
                        @endif
                      </div>
                    </div>
                    <div class="metric-comparison">
                      <div class="metric-bar">
                        <div class="metric-fill" id="sales-fill" style="width: 0;"></div>
                      </div>
                      <span class="small text-muted">vs prev</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Revenue Card -->
              <div class="col-xxl-4 col-md-6">
                <div class="card info-card revenue-card">
                  <div class="card-body">
                    <h5 class="card-title">Revenue <span>| {{ $periodLabel ?? 'This Period' }}</span></h5>
                    <div class="d-flex align-items-center">
                      <div class="card-icon rounded-circle d-flex align-items-center justify-content-center bg-success-light">
                        <i class="bi bi-currency-dollar text-success"></i>
                      </div>
                      <div class="ps-3">
                        <h6>{{ number_format($totalRevenue ?? 0, 0) }} RWF</h6>
                        @if(!empty($hasComparison))
                          <span class="{{ ($revenuePercentChange ?? 0) >= 0 ? 'text-success' : 'text-danger' }} small pt-1 fw-bold">
                            {{ ($revenuePercentChange ?? 0) >= 0 ? '+' : '' }}{{ $revenuePercentChange ?? 0 }}%
                          </span>
                          <span class="text-muted small pt-2 ps-1">{{ $comparisonText ?? '' }}</span>
                        @endif
                      </div>
                    </div>
                    <div class="metric-comparison">
                      <div class="metric-bar">
                        <div class="metric-fill" id="revenue-fill" style="width: 0;"></div>
                      </div>
                      <span class="small text-muted">vs prev</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Quick Actions Card -->
              <div class="col-xxl-4 col-md-6">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title">Quick Actions</h5>
                    <div class="d-grid gap-2">
                      <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm quick-action-btn">
                        <i class="bi bi-plus-circle me-1"></i> Add New Sale
                      </a>
                      <a href="{{ route('customers.create') }}" class="btn btn-outline-primary btn-sm quick-action-btn">
                        <i class="bi bi-person-plus me-1"></i> Register Customer
                      </a>
                      <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary btn-sm quick-action-btn">
                        <i class="bi bi-file-earmark-text me-1"></i> Generate Report
                      </a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Sales Chart Section -->
              <div class="col-12">
                <div class="card h-100">
                  <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <h5 class="card-title mb-0">Sales Performance <span class="text-muted">| {{ $periodLabel ?? '' }}</span></h5>
                      <div class="dropdown">
                        @php
                          $periodMap = ['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year','all'=>'All Time'];
                        @endphp
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="chartPeriodDropdown" data-bs-toggle="dropdown">
                          <span id="currentChartPeriod">{{ $periodMap[$filter ?? 'month'] ?? 'This Month' }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','year'=>'This Year','all'=>'All Time'] as $key=>$label)
                            <li>
                              <a class="dropdown-item chart-period {{ ($filter ?? '')===$key ? 'active' : '' }}" 
                                href="{{ route('dashboard', ['filter'=>$key]) }}">
                                {{ $label }}
                              </a>
                            </li>
                          @endforeach
                        </ul>
                      </div>
                    </div>
                    
                    <div class="chart-controls mb-3 btn-group btn-group-sm" role="group">
                      <button type="button" class="btn btn-outline-primary chart-btn active" data-chart="bar">
                        <i class="bi bi-bar-chart-fill me-1"></i> Bar
                      </button>
                      <button type="button" class="btn btn-outline-primary chart-btn" data-chart="line">
                        <i class="bi bi-graph-up me-1"></i> Line
                      </button>
                      <button type="button" class="btn btn-outline-primary chart-btn" data-chart="doughnut">
                        <i class="bi bi-pie-chart-fill me-1"></i> Pie
                      </button>
                    </div>
                    
                    <div class="chart-container position-relative">
                      <div class="chart-loading d-none" id="sales-chart-loading">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="mt-2">Loading sales data...</p>
                      </div>
                      
                      <canvas id="salesChart"></canvas>
                      
                      <div class="chart-no-data d-none text-center py-4" id="no-sales-data">
                        <i class="bi bi-exclamation-circle fs-1 text-muted"></i>
                        <p class="mt-2 mb-0">
                          @if(($salesCount ?? 0) > 0)
                            Could not load chart data
                          @else
                            No sales data available for selected period
                          @endif
                        </p>
                      </div>
                    </div>
                    
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                      <small class="text-muted">
                        <span id="chartDataInfo">
                          @if(($salesCount ?? 0) > 0)
                            {{ number_format($salesCount) }} sales • {{ number_format($totalRevenue ?? 0) }} RWF
                          @endif
                        </span>
                      </small>
                      <small class="text-muted">Last updated: {{ \Carbon\Carbon::parse($lastUpdated ?? now())->format('M j, Y H:i') }}</small>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Top Products & Recent Sales -->
              <div class="col-xxl-6 col-lg-6">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title mb-3">Top Products <span class="text-muted">| {{ $periodLabel ?? '' }}</span></h5>
                    @if(!empty($topProducts) && count($topProducts))
                      <div class="table-responsive">
                        <table class="table table-hover align-middle">
                          <thead>
                            <tr>
                              <th>#</th>
                              <th>Product</th>
                              <th class="text-end">Qty Sold</th>
                              <th class="text-end">Revenue (RWF)</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($topProducts as $i => $p)
                              <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $p->product_name ?? $p['product_name'] ?? '—' }}</td>
                                <td class="text-end">{{ number_format($p->total_quantity ?? $p['total_quantity'] ?? 0) }}</td>
                                <td class="text-end">{{ number_format($p->total_revenue ?? $p['total_revenue'] ?? 0) }}</td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @else
                      <div class="no-data">No product performance data for this period.</div>
                    @endif
                  </div>
                </div>
              </div>

              <div class="col-xxl-6 col-lg-6">
                <div class="card">
                  <div class="card-body">
                    <h5 class="card-title mb-3">Latest Sales <span class="text-muted">| {{ $periodLabel ?? '' }}</span></h5>
                    @if(!empty($recentSales) && count($recentSales))
                      <div class="table-responsive">
                        <table class="table table-hover align-middle">
                          <thead>
                            <tr>
                              <th>Date</th>
                              <th>Customer</th>
                              <th>Product</th>
                              <th class="text-end">Qty</th>
                              <th class="text-end">Total (RWF)</th>
                              <th>By</th>
                            </tr>
                          </thead>
                          <tbody>
                            @foreach($recentSales as $s)
                              @php
                                $sale = (object)$s;
                                $customer = (object)($sale->customer ?? []);
                                $employee = (object)($sale->employee ?? []);
                                $stockType = (object)($sale->stock_type ?? $sale->stockType ?? []);
                              @endphp
                              <tr>
                                <td>{{ \Carbon\Carbon::parse($sale->sale_date ?? now())->format('Y-m-d H:i') }}</td>
                                <td>{{ $customer->name ?? 'Walk-in' }}</td>
                                <td>{{ $stockType->name ?? '—' }}</td>
                                <td class="text-end">{{ number_format($sale->quantity ?? 0) }}</td>
                                <td class="text-end">{{ number_format($sale->total_price ?? 0) }}</td>
                                <td>
                                  <span class="pill bg-light border">{{ $employee->name ?? '—' }}</span>
                                </td>
                              </tr>
                            @endforeach
                          </tbody>
                        </table>
                      </div>
                    @else
                      <div class="no-data">No recent sales in this period.</div>
                    @endif
                  </div>
                </div>
              </div>

              <!-- Recent Activity Section -->
         <!-- Recent Activity Section -->
<div class="col-12">
  <div class="card">
    <div class="card-body p-2">
      <!-- Calendar Header -->
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="card-title mb-0 fs-6" id="calendarMonthYear">Loading...</h6>
        <div class="btn-group btn-group-sm">
          <button class="btn btn-outline-secondary" id="prevMonth">
            <i class="bi bi-chevron-left"></i>
          </button>
          <button class="btn btn-outline-secondary" id="nextMonth">
            <i class="bi bi-chevron-right"></i>
          </button>
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="calendar-compact">
        <div class="calendar-weekdays">
          <div class="weekday">S</div>
          <div class="weekday">M</div>
          <div class="weekday">T</div>
          <div class="weekday">W</div>
          <div class="weekday">T</div>
          <div class="weekday">F</div>
          <div class="weekday">S</div>
        </div>
        <div class="calendar-days" id="calendarDays"></div>
      </div>
    </div>
  </div>
</div>
            </div><!-- /row -->
          </div>
        </div>
      </section>
    </main>

    @include('theme.footer')

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center">
      <i class="bi bi-arrow-up-short"></i>
    </a>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <script>
    // ====== Server data -> JS ======
    window.dashboardData = {
        filter: @json($filter ?? 'month'),
        salesTrend: @json($salesTrend ?? []),
        topProducts: @json($topProducts ?? []),
        recentSales: @json($recentSales ?? []),
        comparison: {
            sales: @json($salesPercentChange ?? 0),
            revenue: @json($revenuePercentChange ?? 0),
            has: @json($hasComparison ?? false)
        },
        hasSales: @json(($salesCount ?? 0) > 0),
        salesCount: @json($salesCount ?? 0),
        totalRevenue: @json($totalRevenue ?? 0)
    };

    document.addEventListener("DOMContentLoaded", function() {
        // Debug flag
        const debugMode = true;
        
        // ====== Utility Functions ======
        function logDebug(message, data = null) {
            if (debugMode) {
                console.log('[DEBUG] ' + message, data || '');
            }
        }
        
        function formatRWF(n) {
            try {
                return new Intl.NumberFormat('en-KE', { 
                    style: 'currency',
                    currency: 'RWF',
                    maximumFractionDigits: 0
                }).format(n).replace('RWF', '') + 'RWF';
            } catch(e) {
                return Number(n || 0).toLocaleString() + ' RWF';
            }
        }

        // ====== Comparison Bars Animation ======
        function animateComparisonBars() {
            const salesFill = document.getElementById('sales-fill');
            const revenueFill = document.getElementById('revenue-fill');
            
            if (salesFill) {
                const s = Math.max(-100, Math.min(100, Number(window.dashboardData.comparison.sales || 0)));
                salesFill.style.width = Math.abs(s) + '%';
                salesFill.style.background = s >= 0
                    ? 'linear-gradient(90deg, var(--success-color), var(--primary-color))'
                    : 'linear-gradient(90deg, var(--danger-color), var(--warning-color))';
                logDebug('Sales comparison bar set to:', s + '%');
            }
            
            if (revenueFill) {
                const r = Math.max(-100, Math.min(100, Number(window.dashboardData.comparison.revenue || 0)));
                revenueFill.style.width = Math.abs(r) + '%';
                revenueFill.style.background = r >= 0
                    ? 'linear-gradient(90deg, var(--success-color), var(--primary-color))'
                    : 'linear-gradient(90deg, var(--danger-color), var(--warning-color))';
                logDebug('Revenue comparison bar set to:', r + '%');
            }
        }

        // ====== Chart Configuration ======
        const chartConfig = {
            currentChart: null,
            currentType: 'bar',
            colors: {
                primary: 'rgba(78, 115, 223, 0.8)',
                success: 'rgba(28, 200, 138, 0.8)',
                danger: 'rgba(231, 74, 59, 0.8)',
                warning: 'rgba(246, 194, 62, 0.8)',
                info: 'rgba(54, 185, 204, 0.8)',
                purple: 'rgba(103, 114, 229, 0.8)'
            },
            defaultOptions: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 1000,
                    easing: 'easeOutQuart'
                }
            }
        };

        // ====== Data Processing ======
        function normalizeTrendData(trend) {
            // Handle both array format and object format
            if (Array.isArray(trend)) {
                const labels = [];
                const data = [];
                
                trend.forEach(item => {
                    const period = item.period || item.label || '';
                    const value = Number(item.revenue || item.value || item.data || 0);
                    
                    if (period && !isNaN(value)) {
                        labels.push(period);
                        data.push(value);
                    }
                });
                
                return { labels, data };
            } else if (trend && trend.labels && trend.data) {
                // Already in correct format
                return trend;
            }
            
            return { labels: [], data: [] };
        }

        // ====== Chart Management ======
        function getChartOptions(formatFunction) {
            const isDoughnut = chartConfig.currentType === 'doughnut';
            
            return {
                ...chartConfig.defaultOptions,
                plugins: {
                    legend: { 
                        display: isDoughnut, 
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.7)',
                        titleFont: { size: 12 },
                        bodyFont: { size: 12 },
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                const raw = context.raw ?? 0;
                                if (isDoughnut) {
                                    const total = context.dataset.data.reduce((a, b) => a + Number(b || 0), 0);
                                    const pct = total ? (raw / total * 100).toFixed(1) : 0;
                                    return ` ${context.label}: ${formatFunction(raw)} (${pct}%)`;
                                }
                                return ` ${context.label}: ${formatFunction(raw)}`;
                            }
                        }
                    }
                },
                scales: isDoughnut ? {} : {
                    y: {
                        beginAtZero: true,
                        ticks: { 
                            callback: value => formatFunction(value).replace(' RWF', ''),
                            padding: 5
                        },
                        grid: { 
                            drawBorder: false,
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: { 
                        grid: { 
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            };
        }

        function getBackgroundColors() {
            if (chartConfig.currentType === 'doughnut') {
                return [
                    chartConfig.colors.primary,
                    chartConfig.colors.success,
                    chartConfig.colors.danger,
                    chartConfig.colors.warning,
                    chartConfig.colors.info,
                    chartConfig.colors.purple
                ];
            }
            return chartConfig.colors.primary;
        }

        function initChart() {
            const ctx = document.getElementById('salesChart');
            const loadingEl = document.getElementById('sales-chart-loading');
            const noDataEl = document.getElementById('no-sales-data');
            
            if (!ctx) {
                logDebug('Chart canvas element not found');
                return;
            }

            // Clear previous chart if exists
            if (chartConfig.currentChart) {
                chartConfig.currentChart.destroy();
            }

            // Process and validate data
            const trendData = normalizeTrendData(window.dashboardData.salesTrend);
            const hasData = trendData.labels && trendData.labels.length && trendData.data && trendData.data.length;
            
            // Show appropriate state
            if (!hasData) {
                logDebug('No valid chart data available', trendData);
                if (noDataEl) {
                    noDataEl.style.display = 'flex';
                    const msg = window.dashboardData.hasSales 
                        ? 'Chart data unavailable' 
                        : 'No sales data available for selected period';
                    noDataEl.querySelector('p').textContent = msg;
                }
                if (ctx) ctx.style.display = 'none';
                if (loadingEl) loadingEl.style.display = 'none';
                return;
            }

            // Hide no data message
            if (noDataEl) noDataEl.style.display = 'none';
            if (ctx) ctx.style.display = 'block';
            
            // Show loading briefly (even if we have data, for smooth transitions)
            if (loadingEl) loadingEl.style.display = 'flex';
            
            // Initialize chart after short delay
            setTimeout(() => {
                try {
                    if (loadingEl) loadingEl.style.display = 'none';
                    
                    chartConfig.currentChart = new Chart(ctx, {
                        type: chartConfig.currentType,
                        data: {
                            labels: trendData.labels,
                            datasets: [{
                                label: 'Sales Revenue (RWF)',
                                data: trendData.data,
                                backgroundColor: getBackgroundColors(),
                                borderColor: chartConfig.colors.primary.replace('0.8', '1)'),
                                borderWidth: 1,
                                borderRadius: chartConfig.currentType === 'bar' ? 4 : 0,
                                hoverBorderWidth: 2,
                                hoverBackgroundColor: chartConfig.colors.primary.replace('0.8', '0.9)')
                            }]
                        },
                        options: getChartOptions(formatRWF)
                    });
                    
                    logDebug('Chart initialized successfully');
                } catch (e) {
                    console.error('Chart initialization failed:', e);
                    if (noDataEl) {
                        noDataEl.querySelector('p').textContent = 'Error loading chart data';
                        noDataEl.style.display = 'flex';
                    }
                    if (ctx) ctx.style.display = 'none';
                }
            }, 100);
        }

        function updateChartType(type) {
            if (!chartConfig.currentChart) {
                logDebug('No chart available to update');
                return;
            }

            try {
                const ctx = document.getElementById('salesChart');
                const prevChart = chartConfig.currentChart;
                const dataCopy = JSON.parse(JSON.stringify(prevChart.data));
                
                prevChart.destroy();
                chartConfig.currentType = type;
                
                chartConfig.currentChart = new Chart(ctx, {
                    type: type,
                    data: dataCopy,
                    options: getChartOptions(formatRWF)
                });
                
                chartConfig.currentChart.data.datasets[0].backgroundColor = getBackgroundColors();
                chartConfig.currentChart.update();
                
                logDebug('Chart type updated to:', type);
            } catch (e) {
                console.error('Failed to update chart type:', e);
            }
        }

        // ====== Event Handlers ======
        function setupChartTypeButtons() {
            document.querySelectorAll('.chart-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const type = this.dataset.chart;
                    if (chartConfig.currentType === type) return;
                    
                    chartConfig.currentType = type;
                    document.querySelectorAll('.chart-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    updateChartType(type);
                });
            });
        }

        // ====== Initialization ======
        function initializeDashboard() {
            logDebug('Initializing dashboard...');
            
            // Animate comparison bars
            animateComparisonBars();
            
            // Setup chart type buttons
            setupChartTypeButtons();
            
            // Initialize chart
            initChart();
            
            logDebug('Dashboard initialization complete');
        }

        // Start the dashboard
        initializeDashboard();

        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (chartConfig.currentChart) {
                    chartConfig.currentChart.resize();
                }
            }, 200);
        });
    });


    //calendar
    document.addEventListener('DOMContentLoaded', function() {
  // Calendar elements
  const calendarDays = document.getElementById('calendarDays');
  const monthYearEl = document.getElementById('calendarMonthYear');
  const prevMonthBtn = document.getElementById('prevMonth');
  const nextMonthBtn = document.getElementById('nextMonth');
  
  // Current date
  let currentDate = new Date();
  
  // Render calendar
  function renderCalendar() {
    // Clear previous days
    calendarDays.innerHTML = '';
    
    // Set month/year header
    monthYearEl.textContent = new Intl.DateTimeFormat('en-US', {
      month: 'long',
      year: 'numeric'
    }).format(currentDate);
    
    // Get first and last day of month
    const firstDay = new Date(
      currentDate.getFullYear(),
      currentDate.getMonth(),
      1
    );
    const lastDay = new Date(
      currentDate.getFullYear(),
      currentDate.getMonth() + 1,
      0
    );
    
    // Get days from previous month to show
    const prevMonthDays = firstDay.getDay(); // 0-6 (Sun-Sat)
    
    // Get days from next month to show
    const totalDays = prevMonthDays + lastDay.getDate();
    const nextMonthDays = 7 - (totalDays % 7);
    
    // Today's date for comparison
    const today = new Date();
    
    // Add previous month's days
    for (let i = 0; i < prevMonthDays; i++) {
      const day = new Date(
        currentDate.getFullYear(),
        currentDate.getMonth(),
        0 - i
      );
      addDayElement(day, true);
    }
    
    // Add current month's days
    for (let i = 1; i <= lastDay.getDate(); i++) {
      const day = new Date(
        currentDate.getFullYear(),
        currentDate.getMonth(),
        i
      );
      const isToday = 
        day.getDate() === today.getDate() &&
        day.getMonth() === today.getMonth() &&
        day.getFullYear() === today.getFullYear();
      addDayElement(day, false, isToday);
    }
    
    // Add next month's days
    for (let i = 1; i <= nextMonthDays; i++) {
      const day = new Date(
        currentDate.getFullYear(),
        currentDate.getMonth() + 1,
        i
      );
      addDayElement(day, true);
    }
  }
  
  // Add day element to calendar
  function addDayElement(date, isOtherMonth, isToday = false) {
    const dayEl = document.createElement('div');
    dayEl.className = 'calendar-day';
    dayEl.textContent = date.getDate();
    
    if (isOtherMonth) {
      dayEl.classList.add('other-month');
    }
    
    if (isToday) {
      dayEl.classList.add('today');
    }
    
    dayEl.addEventListener('click', function() {
      // Remove selection from other days
      document.querySelectorAll('.calendar-day.selected').forEach(el => {
        el.classList.remove('selected');
      });
      
      // Add selection to clicked day
      this.classList.add('selected');
      
      // You can use the selected date here
      console.log('Selected date:', date);
    });
    
    calendarDays.appendChild(dayEl);
  }
  
  // Event listeners for buttons
  prevMonthBtn.addEventListener('click', function() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar();
  });
  
  nextMonthBtn.addEventListener('click', function() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar();
  });
  
  // Initial render
  renderCalendar();
});
    </script>
  </body>
</html>