@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

{{-- Enhanced Styles --}}
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        --warning-gradient: linear-gradient(135deg, #ffc107 0%, #ff8906 100%);
        --danger-gradient: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        --info-gradient: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
        --glass-bg: rgba(255, 255, 255, 0.1);
        --glass-border: rgba(255, 255, 255, 0.2);
    }

    .main {
        animation: fadeInUp 0.8s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .pagetitle h1 {
        animation: slideInLeft 0.6s ease-out;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .filter-form {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        animation: slideInDown 0.6s ease-out 0.2s both;
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-control, .form-select {
        border-radius: 15px;
        padding: 12px 16px;
        transition: all 0.3s ease;
        border: 2px solid transparent;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        transform: translateY(-2px);
    }

    .btn-primary {
        background: var(--primary-gradient);
        border: none;
        border-radius: 15px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .enhanced-card {
        border-radius: 20px;
        border: none;
        overflow: hidden;
        position: relative;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        animation: cardSlideUp 0.6s ease-out both;
    }

    @keyframes cardSlideUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .enhanced-card:nth-child(1) { animation-delay: 0.1s; }
    .enhanced-card:nth-child(2) { animation-delay: 0.2s; }
    .enhanced-card:nth-child(3) { animation-delay: 0.3s; }
    .enhanced-card:nth-child(4) { animation-delay: 0.4s; }

    .enhanced-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transform: rotate(45deg);
        transition: all 0.6s ease;
        opacity: 0;
    }

    .enhanced-card:hover::before {
        animation: shimmer 0.8s ease-in-out;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%) translateY(-100%) rotate(45deg);
            opacity: 0;
        }
        50% {
            opacity: 1;
        }
        100% {
            transform: translateX(100%) translateY(100%) rotate(45deg);
            opacity: 0;
        }
    }

    .enhanced-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    }

    .bg-gradient.bg-info {
        background: var(--info-gradient) !important;
    }

    .bg-gradient.bg-primary {
        background: var(--primary-gradient) !important;
    }

    .bg-gradient.bg-danger {
        background: var(--danger-gradient) !important;
    }

    .bg-gradient.bg-success {
        background: var(--success-gradient) !important;
    }

    .bg-gradient.bg-warning {
        background: var(--warning-gradient) !important;
    }

    .card-body h3 {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0.5rem 0;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        animation: countUp 1.5s ease-out;
    }

    @keyframes countUp {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .card-body i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        transition: all 0.3s ease;
    }

    .enhanced-card:hover .card-body i {
        transform: scale(1.2) rotate(10deg);
    }

    .alert-enhanced {
        background: linear-gradient(135deg, rgba(116, 185, 255, 0.2) 0%, rgba(9, 132, 227, 0.2) 100%);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(116, 185, 255, 0.3);
        border-radius: 15px;
        animation: slideInRight 0.6s ease-out 0.4s both;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .low-stock-pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    .btn-outline-secondary {
        border-radius: 15px;
        padding: 12px 24px;
        transition: all 0.3s ease;
        border: 2px solid #6c757d;
    }

    .btn-outline-secondary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(108, 117, 125, 0.3);
    }

    .interactive-icon {
        transition: all 0.3s ease;
    }

    .interactive-icon:hover {
        transform: rotate(360deg) scale(1.1);
    }

    .number-counter {
        display: inline-block;
    }

    .loading-shimmer {
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% {
            background-position: -200% 0;
        }
        100% {
            background-position: 200% 0;
        }
    }

    .floating-action {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
    }

    .floating-btn {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: var(--primary-gradient);
        border: none;
        color: white;
        font-size: 1.5rem;
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-10px);
        }
    }

    .floating-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 12px 35px rgba(102, 126, 234, 0.5);
    }
</style>

<main id="main" class="main">
    <div class="pagetitle d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-database-check interactive-icon"></i> Production Summary</h1>
    </div>

    {{-- 🔍 ENHANCED FILTER FORM --}}
    <form method="GET" class="row mb-4 filter-form" id="filterForm">
        <div class="col-md-3">
            <label class="form-label"><i class="bi bi-funnel"></i> Filter</label>
            <select name="filter" class="form-select" onchange="this.form.submit()" id="filterSelect">
                <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Today</option>
                <option value="week" {{ request('filter') == 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ request('filter') == 'month' ? 'selected' : '' }}>This Month</option>
                <option value="year" {{ request('filter') == 'year' ? 'selected' : '' }}>This Year</option>
                <option value="custom" {{ request('filter') == 'custom' ? 'selected' : '' }}>Between Dates</option>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label"><i class="bi bi-calendar-event"></i> From</label>
            <input type="date" name="start_date" class="form-control" id="startDate"
                   value="{{ request('start_date') }}"
                   {{ request('filter') != 'custom' ? 'disabled' : '' }}>
        </div>

        <div class="col-md-3">
            <label class="form-label"><i class="bi bi-calendar-check"></i> To</label>
            <input type="date" name="end_date" class="form-control" id="endDate"
                   value="{{ request('end_date') }}"
                   {{ request('filter') != 'custom' ? 'disabled' : '' }}>
        </div>

        <div class="col-md-3 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-filter"></i> Apply Filter
            </button>
        </div>
    </form>

    @if($start && $end)
        <div class="alert alert-info alert-enhanced">
            <i class="bi bi-info-circle"></i>
            Showing data from <strong>{{ $start->format('Y-m-d') }}</strong>
            to <strong>{{ $end->format('Y-m-d') }}</strong>
        </div>
    @endif

    {{-- ✅ ENHANCED STAT BOXES WITH REAL DATA --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card shadow bg-gradient bg-info text-white enhanced-card">
                <div class="card-body text-center">
                    <i class="bi bi-bar-chart stat-icon"></i>
                    <h5>Produced ({{ ucfirst($filter ?? 'today') }})</h5>
                    <h3 class="number-counter" data-target="{{ $produced }}">0</h3>
                    @if($total > 0)
                        <div class="progress mt-2" style="height: 8px; background: rgba(255,255,255,0.2);">
                            <div class="progress-bar bg-light" style="width: {{ min(($produced / $total) * 100, 100) }}%"></div>
                        </div>
                        <small class="text-light">{{ number_format(($produced / $total) * 100, 1) }}% of total</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card shadow bg-gradient bg-primary text-white enhanced-card">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam stat-icon"></i>
                    <h5>Total Produced (Current)</h5>
                    <h3 class="number-counter" data-target="{{ $total }}">0</h3>
                   
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            <div class="card shadow bg-gradient bg-danger text-white enhanced-card">
                <div class="card-body text-center">
                    <i class="bi bi-truck stat-icon"></i>
                    <h5>Taken to Stock</h5>
                    <h3 class="number-counter" data-target="{{ $used }}">0</h3>
                    @if($total > 0)
                        <div class="progress mt-2" style="height: 8px; background: rgba(255,255,255,0.2);">
                            <div class="progress-bar bg-light" style="width: {{ min(($used / $total) * 100, 100) }}%"></div>
                        </div>
                        <small class="text-light">{{ number_format(($used / $total) * 100, 1) }}% utilized</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6">
            @php
                $stockPercentage = $total > 0 ? ($remaining / $total) * 100 : 0;
                $isLowStock = $remaining < 500 || $stockPercentage < 10;
            @endphp
            <div class="card shadow {{ $isLowStock ? 'bg-gradient bg-warning text-dark low-stock-pulse' : 'bg-gradient bg-success text-white' }} enhanced-card">
                <div class="card-body text-center">
                    <i class="bi bi-stack stat-icon"></i>
                    <h5>
                        Remaining Stock 
                        @if($isLowStock) 
                            <span class="badge {{ $isLowStock ? 'bg-danger text-white' : 'bg-dark text-warning' }}">
                                <i class="bi bi-exclamation-triangle"></i> 
                                {{ $stockPercentage < 5 ? 'Critical' : 'Low' }}
                            </span> 
                        @endif
                    </h5>
                    <h3 class="number-counter" data-target="{{ $remaining }}">0</h3>
                    @if($total > 0)
                        <div class="progress mt-2" style="height: 8px; background: rgba(0,0,0,0.2);">
                            <div class="progress-bar {{ $stockPercentage < 10 ? 'bg-danger' : ($stockPercentage < 25 ? 'bg-warning' : 'bg-success') }}" 
                                 style="width: {{ $stockPercentage }}%"></div>
                        </div>
                        <small class="{{ $isLowStock ? 'text-dark' : 'text-light' }}">
                            {{ number_format($stockPercentage, 1) }}% remaining
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 📊 ADDITIONAL INSIGHTS --}}
    @if($total > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card glass-card text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-graph-up"></i> Production Insights</h5>
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h6>Production Rate</h6>
                            <span class="h4">{{ number_format(($produced / max(1, $start->diffInDays($end))) , 1) }}</span>
                            <small class="d-block">units/day</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6>Stock Status</h6>
                            <span class="h4 {{ $stockPercentage < 25 ? 'text-warning' : 'text-success' }}">
                                {{ $stockPercentage < 10 ? 'Critical' : ($stockPercentage < 25 ? 'Low' : 'Good') }}
                            </span>
                            <small class="d-block">{{ number_format($stockPercentage, 1) }}% left</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6>Utilization</h6>
                            <span class="h4">{{ number_format(($used / $total) * 100, 1) }}%</span>
                            <small class="d-block">of total production</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h6>Days Until Depletion</h6>
                            @php
                                $dailyUsage = $used / max(1, \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::now()->startOfYear()));
                                $daysLeft = $dailyUsage > 0 ? ceil($remaining / $dailyUsage) : 999;
                            @endphp
                            <span class="h4 {{ $daysLeft < 30 ? 'text-danger' : ($daysLeft < 90 ? 'text-warning' : 'text-success') }}">
                                {{ $daysLeft > 365 ? '365+' : $daysLeft }}
                            </span>
                            <small class="d-block">days approx.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="d-flex gap-3 flex-wrap">
        <a href="{{ route('productions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left-circle"></i> Back to Production Records
        </a>
        <button class="btn btn-primary" onclick="refreshData()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>

    {{-- Floating Action Button --}}
    <div class="floating-action">
        <button class="floating-btn" onclick="scrollToTop()" title="Back to Top">
            <i class="bi bi-arrow-up"></i>
        </button>
    </div>
</main>

{{-- Enhanced JavaScript --}}
<script>
    // Initialize animations on page load
    document.addEventListener('DOMContentLoaded', function() {
        initializeCounters();
        initializeInteractions();
    });

    // Counter animation for numbers
    function initializeCounters() {
        const counters = document.querySelectorAll('.number-counter');
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target')) || 0;
            const duration = 2000; // 2 seconds
            const increment = target / (duration / 16); // 60fps
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                counter.textContent = new Intl.NumberFormat().format(Math.floor(current));
            }, 16);
        });
    }

    // Handle filter change
    document.addEventListener('DOMContentLoaded', function() {
        const filterSelect = document.getElementById('filterSelect');
        const startDate = document.getElementById('startDate');
        const endDate = document.getElementById('endDate');
        
        // Set initial state
        if (filterSelect.value === 'custom') {
            startDate.disabled = false;
            endDate.disabled = false;
        }
        
        // Handle filter change
        filterSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                startDate.disabled = false;
                endDate.disabled = false;
                startDate.focus();
                // Don't auto-submit for custom, wait for dates
            } else {
                startDate.disabled = true;
                endDate.disabled = true;
                // Auto-submit for preset options
                this.form.submit();
            }
        });
    });

    // Show loading state on button
    function showLoading(button) {
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
        button.disabled = true;
        
        // Re-enable after form submission
        setTimeout(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        }, 2000);
    }

    // Refresh data functionality
    function refreshData() {
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        
        button.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refreshing...';
        button.disabled = true;
        
        // Add loading shimmer to cards
        document.querySelectorAll('.enhanced-card').forEach(card => {
            card.classList.add('loading-shimmer');
        });
        
        // Simulate refresh by reloading the page
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }

    // Scroll to top
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Initialize interactive features
    function initializeInteractions() {
        // Add hover effects to cards
        document.querySelectorAll('.enhanced-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.zIndex = '10';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.zIndex = '1';
            });
        });

        // Add click animation to buttons
        document.querySelectorAll('.btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255, 255, 255, 0.5);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s linear;
                    pointer-events: none;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    }

    // Add ripple animation CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Auto-hide floating button when at top
    window.addEventListener('scroll', function() {
        const floatingBtn = document.querySelector('.floating-btn');
        if (window.scrollY > 300) {
            floatingBtn.style.opacity = '1';
            floatingBtn.style.visibility = 'visible';
        } else {
            floatingBtn.style.opacity = '0';
            floatingBtn.style.visibility = 'hidden';
        }
    });
</script>

@include('theme.footer')