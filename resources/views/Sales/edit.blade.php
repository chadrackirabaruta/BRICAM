@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Edit Brick Sale #{{ $sale->id }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                <li class="breadcrumb-item active">Edit Sale</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Edit Sale Details</h5>

                        @if(!$stockType)
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-octagon me-1"></i> 
                                No stock type configured for sales. Please configure stock types first.
                            </div>
                        @else
              <form action="{{ route('sales.update', $sale->id) }}" method="POST" id="saleForm">
    @csrf
    @method('PUT')

    <!-- Status & Restriction Info -->
    <div class="alert alert-info d-flex align-items-center">
        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
        <div>
            <strong>Original Sale Date:</strong> {{ $sale->created_at->format('M d, Y h:i A') }}
            <span class="badge bg-{{ $sale->status_color ?? 'secondary' }} ms-2">
                {{ ucfirst($sale->status ?? 'Unknown') }}
            </span>

            @if(now()->diffInMinutes($sale->created_at) > 60)
                <div class="mt-2 text-danger fw-bold">
                    <i class="bi bi-lock-fill me-1"></i>
                    Editing is disabled (more than 1 hour since creation).
                </div>
            @endif
        </div>
    </div>

    <fieldset @if(now()->diffInMinutes($sale->created_at) > 60) disabled @endif>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Customer <span class="text-danger">*</span></label>
                <select name="customer_id" class="form-select select2" required>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" 
                            {{ old('customer_id', $sale->customer_id) == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} ({{ $customer->phone ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                @error('customer_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Sales Person <span class="text-danger">*</span></label>
                <select name="employee_id" class="form-select select2" required>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" 
                            {{ old('employee_id', $sale->employee_id) == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name ?? '[Unknown User]' }}
                        </option>
                    @endforeach
                </select>
                @error('employee_id')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Stock & Quantity -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Available Stock</label>
                <div class="input-group">
                    <input type="text" class="form-control" 
                           value="{{ number_format($availableStock ?? 0) }}" readonly>
                    <span class="input-group-text">{{ $stockType->name ?? '' }}</span>
                </div>
            </div>

            <div class="col-md-4">
                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                <input type="number" name="quantity" id="quantity" 
                       class="form-control @error('quantity') is-invalid @enderror" 
                       min="1" max="{{ $availableStock ?? 0 }}" 
                       value="{{ old('quantity', $sale->quantity ?? 0) }}" required>
                @error('quantity')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Original: {{ $sale->quantity ?? 0 }}</small>
            </div>

            <div class="col-md-4">
                <label class="form-label">Unit Price <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" name="unit_price" id="unit_price" 
                           class="form-control @error('unit_price') is-invalid @enderror" 
                           step="0.01" min="0" 
                           value="{{ old('unit_price', $sale->unit_price ?? 0) }}" required>
                    @error('unit_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted w-100 d-block">
                        Original: ${{ number_format($sale->unit_price ?? 0, 2) }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Total & Payment -->
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Total Amount</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="text" class="form-control" id="total_price" 
                           value="{{ number_format(($sale->quantity ?? 0) * ($sale->unit_price ?? 0), 2) }}" readonly>
                    <small class="text-muted w-100 d-block">
                        Original: ${{ number_format($sale->total_price ?? 0, 2) }}
                    </small>
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                <select name="payment_method" class="form-select" required>
                    @foreach($paymentMethods as $method)
                        <option value="{{ $method }}" 
                            {{ old('payment_method', $sale->payment_method) == $method ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $method)) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Notes -->
        <div class="row mb-3">
            <div class="col-12">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3">{{ old('notes', $sale->notes ?? '') }}</textarea>
            </div>
        </div>
    </fieldset>

    <div class="text-center mt-4">
        @if(now()->diffInMinutes($sale->created_at) <= 60)
            <button type="submit" class="btn btn-primary px-4">
                <i class="bi bi-save me-1"></i> Update Sale
            </button>
        @else
            <button type="button" class="btn btn-secondary px-4" disabled>
                <i class="bi bi-lock me-1"></i> Editing Disabled
            </button>
        @endif

        <a href="{{ route('sales.receipt', $sale->id) }}" class="btn btn-outline-secondary ms-2">
            <i class="bi bi-eye me-1"></i> View Details
        </a>
        <a href="{{ route('sales.index') }}" class="btn btn-secondary ms-2">
            <i class="bi bi-x-circle me-1"></i> Cancel
        </a>
    </div>
</form>

                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

@include('theme.footer')

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const quantityInput = document.getElementById('quantity');
        const unitPriceInput = document.getElementById('unit_price');
        const totalPriceInput = document.getElementById('total_price');
        
        function calculateTotal() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const unitPrice = parseFloat(unitPriceInput.value) || 0;
            totalPriceInput.value = (quantity * unitPrice).toFixed(2);
        }
        
        quantityInput.addEventListener('input', calculateTotal);
        unitPriceInput.addEventListener('input', calculateTotal);
        calculateTotal();
        
        if($().select2) {
            $('.select2').select2({ theme: 'bootstrap-5' });
        }

        let formChanged = false;
        $('#saleForm :input').change(function() { formChanged = true; });
        window.addEventListener('beforeunload', function(e) {
            if(formChanged) { e.preventDefault(); e.returnValue = ''; }
        });
    });
</script>
@endpush
