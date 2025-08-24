@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<style>
    :root {
        --primary-color: #0d6efd;
        --success-color: #198754;
        --sidebar-width: 250px;
        --header-height: 60px;
    }

    /* Enhanced Main Content */
    .main {
        transition: margin-left 0.3s ease;
        min-height: calc(100vh - var(--header-height));
    }

    /* Enhanced Card Styles */
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        transition: box-shadow 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    /* Enhanced Page Title */
    .pagetitle h1 {
        color: #2c3e50;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .pagetitle .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    /* Enhanced Table Styles */
    .table-container {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        position: relative;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        border: none;
        font-weight: 600;
        position: sticky;
        top: 0;
        z-index: 10;
        white-space: nowrap;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .table tbody tr {
        transition: background-color 0.2s ease;
    }

    .table tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }

    .table tfoot {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        font-weight: 600;
    }

    /* Enhanced Form Controls */
    .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #e0e6ed;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    /* Enhanced Buttons */
    .btn {
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Salary cell styling */
    .salary-cell {
        position: relative;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .salary-cell:hover {
        background-color: rgba(13, 110, 253, 0.1);
        transform: scale(1.02);
    }

    /* Weekend and Holiday styling */
    .weekend-cell {
        background-color: #f8f9fa !important;
        color: #6c757d;
    }

    .holiday-cell {
        background-color: #fff3cd !important;
        color: #856404;
    }

    .absent-cell {
        background-color: #f8d7da !important;
        color: #721c24;
    }

    /* Loading Animation */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 2000;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid var(--primary-color);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Statistics Cards */
    .stats-card {
        text-align: center;
        transition: transform 0.3s ease;
    }

    .stats-card:hover {
        transform: translateY(-5px);
    }

    .stats-icon {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    /* Filter Form Enhancement */
    .filter-form {
        background: white;
        padding: 1rem;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Weekly totals styling */
    .weekly-total {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white !important;
        font-weight: 600;
    }

    .weekly-header {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        color: white !important;
    }
    @media (max-width: 768px) {
        .pagetitle {
            flex-direction: column;
            align-items: stretch !important;
        }
        
        .filter-form {
            flex-direction: column;
            gap: 1rem;
        }
        
        .filter-form .d-flex {
            flex-direction: column;
            align-items: stretch;
        }
        
        .table-container {
            margin: 0 -1rem;
            border-radius: 0;
        }
    }

    /* Print Styles */
    @media print {
        .btn, .filter-form, .stats-cards {
            display: none !important;
        }
        .main {
            margin: 0 !important;
            padding: 0 !important;
        }
        .table {
            font-size: 10px;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #000 !important;
        }
    }
</style>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>
<main id="main" class="main">
    <div class="pagetitle d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1><i class="bi bi-calendar3 me-2 text-primary"></i>Calendar of Employee Salaries</h1>
            <p class="text-muted mb-0">
                Month: <strong class="text-primary">{{ \Carbon\Carbon::create($year, $month)->format('F Y') }}</strong>
                <span class="badge bg-light text-dark ms-2">{{ $daysInMonth }} Days</span>
            </p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('salaries.export.csv', ['month' => request('month'), 'employee_type' => request('employee_type')]) }}"
               class="btn btn-success" onclick="showExportLoader(this)">
                <i class="bi bi-download"></i>
                <span>Export CSV</span>
            </a>

            <button class="btn btn-info" onclick="printTable()">
                <i class="bi bi-printer"></i>
                <span>Print</span>
            </button>
        </div>
    </div>

    <!-- Enhanced Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="filter-form d-flex align-items-center gap-3 flex-wrap" action="{{ route('salary.All') }}">
                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0 fw-medium text-nowrap">
                        <i class="bi bi-calendar3 me-1"></i>Month:
                    </label>
                    <input type="month" name="month" class="form-control form-control-sm" style="width: 160px"
                           value="{{ $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) }}">
                </div>

                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0 fw-medium text-nowrap">
                        <i class="bi bi-people me-1"></i>Type:
                    </label>
                    <select name="employee_type" class="form-select form-select-sm" style="width: 160px">
                        <option value="">All Types</option>
                        @foreach ($employeeTypes as $type)
                            <option value="{{ $type->name }}" {{ request('employee_type') == $type->name ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel"></i>
                    <span>Apply Filter</span>
                </button>

                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Reset</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4 stats-cards">
        <div class="col-md-3 mb-3">
            <div class="card stats-card">
                <div class="card-body">
                    <i class="bi bi-people stats-icon text-primary"></i>
                    <h4 class="card-title">{{ count($calendarData) }}</h4>
                    <p class="card-text text-muted">Total Employees</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card">
                <div class="card-body">
                    <i class="bi bi-calendar-check stats-icon text-success"></i>
                    <h4 class="card-title">{{ $daysInMonth }}</h4>
                    <p class="card-text text-muted">Calendar Days</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card">
                <div class="card-body">
                    <i class="bi bi-cash-stack stats-icon text-warning"></i>
                    <h4 class="card-title">{{ number_format(array_sum($dailyTotals)) }}</h4>
                    <p class="card-text text-muted">Total Payroll</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card">
                <div class="card-body">
                    <i class="bi bi-graph-up stats-icon text-info"></i>
                    <h4 class="card-title">{{ number_format(array_sum($dailyTotals) / $daysInMonth) }}</h4>
                    <p class="card-text text-muted">Daily Average</p>
                </div>
            </div>
        </div>
    </div>
<section class="section">
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-container">
                <div style="overflow-x: auto; max-height: 70vh;">
                    <table class="table table-bordered table-sm text-center align-middle mb-0" id="salaryTable">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th style="min-width: 50px">#</th>
                                <th style="min-width: 200px" class="text-start">
                                    <i class="bi bi-person me-1"></i>Employee Name
                                </th>
                                @for ($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $currentDate = \Carbon\Carbon::create($year, $month, $day);
                                        $dayOfWeek = $currentDate->dayOfWeek;
                                        $isWeekend = in_array($dayOfWeek, [0, 6]);
                                    @endphp
                                    <th style="min-width: 50px" class="{{ $isWeekend ? 'weekend-cell' : '' }}" 
                                        title="{{ $currentDate->format('l, F j, Y') }}">
                                        {{ $day }}<sup>{{ $currentDate->format('S') }}</sup>
                                        @if($isWeekend)
                                            <br><small class="text-muted">{{ $currentDate->format('D') }}</small>
                                        @endif
                                    </th>
                                    
                                    @if($dayOfWeek == 6 || $day == $daysInMonth)
                                        <th style="min-width: 80px" class="bg-info text-white">
                                            <i class="bi bi-calendar-week me-1"></i>W{{ ceil($day / 7) }}
                                        </th>
                                    @endif
                                @endfor
                                <th style="min-width: 100px" class="bg-primary text-white">
                                    <i class="bi bi-calculator me-1"></i>Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $rowIndex = 1; @endphp
                            @foreach ($calendarData as $employeeRow)
                                @if($employeeRow['employee']->active == 1)
                                    @php
                                        $currentWeek = 1;
                                        $weeklySum = 0;
                                    @endphp
                                    <tr>
                                        <td class="fw-medium">{{ $rowIndex++ }}</td>
                                        <td class="text-start fw-medium">
                                            <i class="bi bi-person-badge me-1 text-muted"></i>
                                            {{ $employeeRow['employee']->name }}
                                        </td>
                                        @for ($day = 1; $day <= $daysInMonth; $day++)
                                            @php
                                                $currentDate = \Carbon\Carbon::create($year, $month, $day);
                                                $dayOfWeek = $currentDate->dayOfWeek;
                                                $isWeekend = in_array($dayOfWeek, [0, 6]);
                                                $amount = $employeeRow['days'][$day] ?? 0;
                                                $cellClass = '';
                                                $tooltip = '';
                                                $weeklySum += $amount;
                                                
                                                if ($isWeekend) {
                                                    $cellClass = 'weekend-cell';
                                                    $tooltip = 'Weekend - ' . $currentDate->format('l');
                                                } elseif ($amount == 0) {
                                                    $cellClass = 'absent-cell';
                                                    $tooltip = 'No salary recorded';
                                                } else {
                                                    $cellClass = 'salary-cell';
                                                    $tooltip = 'Regular workday - ' . $currentDate->format('l');
                                                }
                                            @endphp
                                            <td class="{{ $cellClass }}" 
                                                data-bs-toggle="tooltip" 
                                                title="{{ $tooltip }}"
                                                data-employee="{{ $employeeRow['employee']->name }}"
                                                data-date="{{ $currentDate->format('Y-m-d') }}"
                                                data-amount="{{ $amount }}"
                                                onclick="showSalaryDetail(this)">
                                                {{ number_format($amount) }}
                                            </td>
                                            
                                            @if($dayOfWeek == 6 || $day == $daysInMonth)
                                                <td class="fw-bold text-white bg-info">
                                                    {{ number_format($weeklySum) }}
                                                </td>
                                                @php
                                                    $weeklySum = 0;
                                                    $currentWeek++;
                                                @endphp
                                            @endif
                                        @endfor
                                        <td class="fw-bold text-primary fs-6">
                                            {{ number_format($employeeRow['total']) }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="2" class="text-start">
                                    <i class="bi bi-calculator me-2"></i>TOTAL PER DAY
                                </td>
                                @php
                                    $weeklySum = 0;
                                    $currentWeek = 1;
                                @endphp
                                @for ($day = 1; $day <= $daysInMonth; $day++)
                                    @php
                                        $currentDate = \Carbon\Carbon::create($year, $month, $day);
                                        $dayOfWeek = $currentDate->dayOfWeek;
                                        $isWeekend = in_array($dayOfWeek, [0, 6]);
                                        $dailyAmount = $dailyTotals[$day] ?? 0;
                                        $weeklySum += $dailyAmount;
                                    @endphp
                                    <td class="{{ $isWeekend ? 'weekend-cell' : '' }}">
                                        {{ number_format($dailyAmount) }}
                                    </td>
                                    
                                    @if($dayOfWeek == 6 || $day == $daysInMonth)
                                        <td class="fw-bold text-white bg-info">
                                            {{ number_format($weeklySum) }}
                                        </td>
                                        @php
                                            $weeklySum = 0;
                                            $currentWeek++;
                                        @endphp
                                    @endif
                                @endfor
                                <td class="text-primary fs-5">
                                    {{ number_format(array_sum($dailyTotals)) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
</main>

@push('scripts')
<script>
    function showSalaryDetail(element) {
        const employee = element.dataset.employee || 'N/A';
        const dateStr = element.dataset.date;
        const amount = element.dataset.amount || 0;
        
        // Check if date is valid before creating Date object
        let dateObj, dayName, formattedDate;
        if (dateStr && dateStr !== 'undefined') {
            dateObj = new Date(dateStr);
            // Check if date is valid
            if (!isNaN(dateObj.getTime())) {
                dayName = dateObj.toLocaleDateString('en-US', { weekday: 'long' });
                formattedDate = dateObj.toLocaleDateString();
            } else {
                formattedDate = 'Invalid date';
                dayName = '';
            }
        } else {
            formattedDate = 'N/A';
            dayName = '';
        }
        
        // Format amount if it's a valid number
        let formattedAmount = 'N/A';
        if (amount && !isNaN(parseFloat(amount))) {
            formattedAmount = parseFloat(amount).toLocaleString();
        }
        
        Swal.fire({
            title: 'Salary Details',
            html: `
                <div class="text-start">
                    <p><strong>Employee:</strong> ${employee}</p>
                    <p><strong>Date:</strong> ${formattedDate} ${dayName ? `(${dayName})` : ''}</p>
                    <p><strong>Amount:</strong> ${formattedAmount}</p>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Close'
        });
    }

    function printTable() {
        const printWindow = window.open('', '', 'height=600,width=800');
        printWindow.document.write('<html><head><title>Salary Calendar</title>');
        printWindow.document.write('<link href="{{ asset("css/bootstrap.min.css") }}" rel="stylesheet">');
        printWindow.document.write('<style>body{padding:20px} .weekend-cell{background-color:#f8f9fa} .absent-cell{background-color:#fff3cd} .salary-cell{background-color:#e8f5e9}</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write('<h3>Salary Calendar - {{ \Carbon\Carbon::create($year, $month)->format("F Y") }}</h3>');
        printWindow.document.write(document.getElementById('salaryTable').outerHTML);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    }

    function resetFilters() {
        window.location.href = "{{ route('salary.All') }}";
    }

    function showExportLoader(button) {
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i> Preparing Export...';
        button.disabled = true;
        
        setTimeout(() => {
            button.innerHTML = originalContent;
            button.disabled = false;
        }, 3000);
    }

    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

<style>
    .weekend-cell {
        background-color: #f8f9fa;
        color: #6c757d;
    }
    .absent-cell {
        background-color: #fff3cd;
        color: #856404;
    }
    .salary-cell {
        background-color: #e8f5e9;
    }
    .stats-card {
        height: 100%;
    }
    .stats-icon {
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
    }
    .table-container {
        position: relative;
    }
    .sticky-top {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .total-cell {
        background-color: #e7f5ff;
        font-weight: bold;
    }
</style>

@include('theme.footer')

<script>
    // Loading overlay functions
    function showLoading() {
        document.getElementById('loadingOverlay').style.display = 'flex';
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').style.display = 'none';
    }

    // Export loader
    function showExportLoader(btn) {
        const originalText = btn.innerHTML;
        btn.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div>Exporting...';
        btn.disabled = true;
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            showNotification('CSV export started! Download will begin shortly.', 'success');
        }, 1000);
    }

    // Filter loader - FIXED: Remove loading state properly
    function showFilterLoader(btn) {
        // Don't show loading on the button since it's a form submission
        showLoading();
        // The page will reload, so loading will be hidden automatically
    }

    // Reset filters
    function resetFilters() {
        const form = document.querySelector('.filter-form');
        const monthInput = form.querySelector('input[name="month"]');
        const typeSelect = form.querySelector('select[name="employee_type"]');
        
        monthInput.value = '{{ $year . "-" . str_pad($month, 2, "0", STR_PAD_LEFT) }}';
        typeSelect.value = '';
        
        form.submit();
    }

    // Print functionality
    function printTable() {
        window.print();
    }

    // Show salary detail modal
    function showSalaryDetail(elementOrName, date, amount, dayOfWeek) {
        // Check if first parameter is an HTML element (from onclick="showSalaryDetail(this)")
        let displayName, displayDate, displayDayOfWeek, displayAmount;
        
        if (elementOrName && elementOrName.dataset) {
            // Called with element parameter
            const element = elementOrName;
            displayName = element.dataset.employee || 'N/A';
            const dateStr = element.dataset.date;
            const amountVal = element.dataset.amount || 0;
            
            // Process date
            if (dateStr && dateStr !== 'undefined') {
                const dateObj = new Date(dateStr);
                if (!isNaN(dateObj.getTime())) {
                    displayDate = dateObj.toLocaleDateString();
                    displayDayOfWeek = `(${dateObj.toLocaleDateString('en-US', { weekday: 'long' })})`;
                } else {
                    displayDate = 'Invalid date';
                    displayDayOfWeek = '';
                }
            } else {
                displayDate = 'N/A';
                displayDayOfWeek = '';
            }
            
            // Process amount
            if (amountVal && !isNaN(parseFloat(amountVal))) {
                displayAmount = parseFloat(amountVal).toLocaleString();
            } else {
                displayAmount = 'N/A';
            }
        } else {
            // Called with separate parameters
            displayName = elementOrName || 'N/A';
            displayDate = (date && date !== 'undefined') ? date : 'N/A';
            displayDayOfWeek = (dayOfWeek && dayOfWeek !== 'undefined') ? `(${dayOfWeek})` : '';
            
            // Format amount if it's a valid number
            if (amount && !isNaN(parseFloat(amount))) {
                displayAmount = parseFloat(amount).toLocaleString();
            } else {
                displayAmount = 'N/A';
            }
        }
        
        // Create modal HTML
        const modalHtml = `
            <div class="modal fade" id="salaryDetailModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-info-circle me-2"></i>Salary Details
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong><i class="bi bi-person me-1"></i>Employee:</strong></div>
                                <div class="col-sm-8">${displayName}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong><i class="bi bi-calendar me-1"></i>Date:</strong></div>
                                <div class="col-sm-8">${displayDate} ${displayDayOfWeek}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong><i class="bi bi-cash me-1"></i>Amount:</strong></div>
                                <div class="col-sm-8">
                                    <span class="fs-4 text-primary fw-bold">$${displayAmount}</span>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-4"><strong><i class="bi bi-check-circle me-1"></i>Status:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge bg-success">Processed</span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Remove existing modal if any
        const existingModal = document.getElementById('salaryDetailModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        
        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('salaryDetailModal'));
        modal.show();
        
        // Remove modal from DOM when hidden
        document.getElementById('salaryDetailModal').addEventListener('hidden.bs.modal', function() {
            this.remove();
        });
    }

    // Notification system
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
        
        const iconClass = type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle';
        
        notification.innerHTML = `
            <i class="bi bi-${iconClass} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    // Enhanced table scrolling
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Add horizontal scroll with shift+wheel
        const tableContainer = document.querySelector('[style*="overflow-x: auto"]');
        if (tableContainer) {
            tableContainer.addEventListener('wheel', function(e) {
                if (e.shiftKey) {
                    e.preventDefault();
                    this.scrollLeft += e.deltaY;
                }
            });
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Export with Ctrl+E
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                document.querySelector('a[href*="export"]').click();
            }
            
            // Print with Ctrl+P
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                printTable();
            }
        });

        // Welcome message
        setTimeout(() => {
            showNotification('Salary calendar loaded successfully! Click on any salary cell for details. Use Ctrl+E to export, Ctrl+P to print.', 'info');
        }, 1000);
    });
</script>