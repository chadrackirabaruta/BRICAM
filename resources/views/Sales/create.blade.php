@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
    <div class="pagetitle bg-light p-3 rounded">
        <h1 class="text-primary"><i class="bi bi-bricks me-2"></i> New Brick Sale</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}"><i class="bi bi-house-door"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sales.index') }}"><i class="bi bi-cart"></i> Sales</a></li>
                <li class="breadcrumb-item active text-success"><i class="bi bi-plus-circle"></i> New Sale</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-primary">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-file-earmark-plus me-1"></i> Create New Sale</h5>
                    </div>
                    <div class="card-body">
                        @if(!$stockType)
                            <div class="alert alert-danger">Stock type is not configured.</div>
                        @elseif($availableStock <= 0)
                            <div class="alert alert-warning">No stock available.</div>
                        @else
                        <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
                            @csrf

                           <div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label fw-bold"><i class="bi bi-person me-1"></i> Customer <span class="text-danger">*</span></label>
        <select name="customer_id" class="form-select select2" required>
            <option value="">Select Customer</option>
            @foreach($customers as $customer)
                @if($customer->status === 'active')
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endif
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-bold"><i class="bi bi-person-badge me-1"></i> Sales Person <span class="text-danger">*</span></label>
        <select name="employee_id" class="form-select select2" required>
            <option value="">Select Employee</option>
            @foreach($employees as $employee)
                @if($employee->active == 1)
                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                @endif
            @endforeach
        </select>
    </div>
</div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold"><i class="bi bi-box-seam me-1"></i> Available Stock</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control bg-light text-dark" value="{{ $availableStock }}" readonly>
                                        <span class="input-group-text bg-success text-white">{{ $stockType->name }}</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold"><i class="bi bi-123 me-1"></i> Quantity <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-box"></i></span>
                                        <input type="number" name="quantity" id="quantity" class="form-control"
                                               min="1" max="{{ $availableStock }}" required>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold"><i class="bi bi-tag me-1"></i> Unit Price <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="unit_price" id="unit_price"
                                               class="form-control" step="0.01" min="0" value="30" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold"><i class="bi bi-calculator me-1"></i> Total Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text" id="total_price"
                                               class="form-control bg-light text-success fw-bold"
                                               placeholder="0.00" readonly>
                                    </div>
                                    <small class="text-muted">Calculated automatically as Quantity × Unit Price</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold"><i class="bi bi-credit-card me-1"></i> Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_method" class="form-select" required>
                                        @foreach($paymentMethods as $method)
                                            <option value="{{ $method }}">{{ ucfirst($method) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold"><i class="bi bi-pencil-square me-1"></i> Notes</label>
                                <textarea name="notes" class="form-control" placeholder="Any special instructions..." rows="3"></textarea>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-cart-check me-1"></i> Complete Sale
                                </button>
                                <a href="{{ route('sales.index') }}" class="btn btn-outline-danger ms-2">
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
document.addEventListener('DOMContentLoaded', () => {
    const quantityInput = document.getElementById('quantity');
    const unitPriceInput = document.getElementById('unit_price');
    const totalPriceInput = document.getElementById('total_price');
    const maxQty = parseInt(quantityInput?.getAttribute('max')) || 1000000;

    function formatCurrency(value) {
        return parseFloat(value || 0).toFixed(2);
    }

    function calculateTotal() {
        const quantity = parseFloat(quantityInput.value) || 0;
        let unitPrice = parseFloat(unitPriceInput.value) || 0;

        if (quantity > maxQty) {
            quantityInput.value = maxQty;
        }

        const total = quantity * unitPrice;
        totalPriceInput.value = formatCurrency(total);

        // Optional debug
        console.log(`QTY: ${quantity}, PRICE: ${unitPrice}, TOTAL: ${total}`);
    }

    if (quantityInput && unitPriceInput) {
        quantityInput.addEventListener('input', calculateTotal);
        unitPriceInput.addEventListener('input', calculateTotal);

        calculateTotal(); // Init
    }

    // Initialize Select2 (optional)
    if (window.$ && $.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select an option...',
            allowClear: true,
        });
    }
});
</script>
@endpush
@push('styles')
<style>
    #total_price {
        transition: all 0.3s ease;
    }
</style>
@endpush

<style>
    .card {
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .form-control:focus, .form-select:focus {
        border-color: #86b7fe;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    .input-group-text {
        background-color: #f8f9fa;
    }
    .breadcrumb {
        background-color: #f8f9fa;
        padding: 0.75rem 1rem;
        border-radius: 0.375rem;
    }
    .alert {
        border-left: 5px solid;
    }
    .alert-danger {
        border-left-color: #dc3545;
    }
    .alert-warning {
        border-left-color: #ffc107;
    }
    #total_price {
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }
    #recalculateBtn:hover {
        background-color: #e9ecef;
    }
</style>