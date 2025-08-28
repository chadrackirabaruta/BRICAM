@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
    <div class="pagetitle d-flex justify-content-between align-items-center">
        <h1>Sales History</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item active">Sales</li>
            </ol>
        </nav>
    </div>
    @include('theme.success')
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title">All Sales Records</h5>
                            <a href="{{ route('sales.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> New Sale
                            </a>
                        </div>
                        <!-- Filter Form -->
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="customer_id" class="form-select">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="employee_id" class="form-select">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="bi bi-filter"></i> Filter
                                </button>
                                <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </a>
                            </div>
                        </form>

                        <!-- Sales Table -->
                        @if($sales->isEmpty())
                            <div class="alert alert-info">
                                No sales records found. <a href="{{ route('sales.create') }}">Create your first sale</a>.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped table-hover align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Employee</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                            <th>Payment</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sales as $sale)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                                <td>{{ $sale->customer->name }}</td>
                                                <td>{{ $sale->user->name ?? '[Unknown User]' }}</td>
                                                <td>{{ number_format($sale->quantity) }} {{ $sale->stockType->name ?? '' }}</td>
                                                <td>{{ number_format($sale->unit_price, 2) }}</td>
                                                <td>{{ number_format($sale->total_price, 2) }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $sale->payment_method === 'credit' ? 'warning' : 'success' }}">
                                                        {{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <!-- Receipt -->
                                                        <a href="{{ route('sales.receipt', $sale->id) }}" 
                                                           class="btn btn-outline-info" 
                                                           title="View Receipt" 
                                                           data-bs-toggle="tooltip" 
                                                           data-bs-placement="top">
                                                            <i class="bi bi-receipt"></i> 
                                                            <span class="d-none d-sm-inline ms-1">Receipt</span>
                                                        </a>

                                                        <!-- Edit -->
                                                        <a href="{{ route('sales.edit', $sale->id) }}" 
                                                           class="btn btn-outline-primary" 
                                                           title="Edit Sale" 
                                                           data-bs-toggle="tooltip" 
                                                           data-bs-placement="top">
                                                            <i class="bi bi-pencil-square"></i> 
                                                            <span class="d-none d-sm-inline ms-1">Edit</span>
                                                        </a>

                                                        <!-- Return / Delete -->
                                                        <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline" 
                                                              onsubmit="return confirm('Are you sure you want to return this sale? This will restore the stock.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-outline-warning" title="Return Sale">
                                                                <i class="bi bi-arrow-counterclockwise"></i> 
                                                                <span class="d-none d-sm-inline ms-1">Return</span>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-3">
                                {{ $sales->withQueryString()->links() }}
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

@include('theme.footer')

@push('styles')
<style>
    .btn-group .btn {
        transition: all 0.3s ease;
        border-radius: 0.25rem !important;
        margin-right: 0.25rem;
    }
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    .btn-group .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush
