@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
    <!-- Page Title -->
    <div class="pagetitle d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-receipt"></i> Sale Receipt</h1>
        
        <!-- Action Buttons -->
        <div class="d-print-none">
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-primary me-2">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Sales
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card printable-area" id="printable-receipt">
                    <div class="card-body">
                        {{-- HEADER --}}
                        <div class="row mb-4">
                            <div class="col-md-6 d-flex align-items-center">
                                <img src="{{ asset('assets/img/logo.png') }}" alt="Logo" style="height: 80px;" class="me-3">
                                <div>
                                    <h2 class="mb-0">{{ config('app.name', 'Brick Factory') }}</h2>
                                    <p class="mb-0 text-muted">
                                        {{ config('company.address', '123 Factory Road') }}<br>
                                        {{ config('company.city', 'Kigali, Rwanda') }}<br>
                                        Tel: {{ config('company.phone', '+250 123 456 789') }}
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6 text-end">
                                <h3 class="text-primary">RECEIPT</h3>
                                <p><strong>Receipt No:</strong> {{ $sale->reference_number }}</p>
                                <p><strong>Date:</strong> {{ $sale->sale_date->format('d M Y H:i') }}</p>
                                <p><strong>Sales Person:</strong> {{ $sale->employee->name }}</p>
                            </div>
                        </div>

                        {{-- CUSTOMER AND PAYMENT --}}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">Customer Information</div>
                                    <div class="card-body">
                                        <p><strong>Name:</strong> {{ $sale->customer->name }}</p>
                                        <p><strong>Phone:</strong> {{ $sale->customer->phone ?? 'N/A' }}</p>
                                        <p><strong>Email:</strong> {{ $sale->customer->email ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">Payment Information</div>
                                    <div class="card-body">
                                        <p><strong>Method:</strong> {{ ucfirst(str_replace('_', ' ', $sale->payment_method)) }}</p>
                                        <p><strong>Total:</strong> {{ number_format($sale->total_price, 2) }} RWF</p>
                                        @if($sale->payment_method === 'credit')
                                            <p><strong>Balance:</strong> <span class="{{ $sale->balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($sale->balance, 2) }} RWF</span></p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ITEMS TABLE --}}
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Description</th>
                                        <th class="text-end">Qty</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-end">Amount (RWF)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $sale->stockType->name }} Bricks</td>
                                        <td class="text-end">{{ number_format($sale->quantity) }}</td>
                                        <td class="text-end">{{ number_format($sale->unit_price, 2) }}</td>
                                        <td class="text-end">{{ number_format($sale->total_price, 2) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <th colspan="3" class="text-end">Total:</th>
                                        <th class="text-end">{{ number_format($sale->total_price, 2) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        {{-- FOOTER --}}
                        <div class="text-center mt-4">
                            <p>Thank you for your business!</p>
                            <div class="signature-area mt-4 mb-2">
                                <div style="border-top: 1px solid #000; width: 200px; margin: 0 auto;"></div>
                                <p class="mt-2">Authorized Signature</p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

{{-- Styles --}}
<style>
    .printable-area {
        background: white;
        padding: 20px;
        border-radius: 5px;
    }
    @media print {
        body * {
            visibility: hidden !important;
        }
        .printable-area, .printable-area * {
            visibility: visible !important;
        }
        .printable-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0;
            margin: 0;
        }
        .d-print-none {
            display: none !important;
        }
    }
</style>

@include('theme.footer')
