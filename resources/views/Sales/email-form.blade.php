@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Email Receipt</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sales.index') }}">Sales</a></li>
                <li class="breadcrumb-item"><a href="{{ route('sales.receipt', $sale->id) }}">Receipt #{{ $sale->reference_number }}</a></li>
                <li class="breadcrumb-item active">Email Receipt</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Email Receipt #{{ $sale->reference_number }}</h5>
                        
                        <!-- Floating Labels Form -->
                        <form class="row g-3" action="{{ route('sales.email.send', $sale->id) }}" method="POST">
                            @csrf
                            
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="floatingEmail" placeholder="Recipient Email"
                                           value="{{ old('email', $sale->customer->email) }}" required>
                                    <label for="floatingEmail">Recipient Email</label>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="form-floating">
                                    <input type="text" name="subject" class="form-control @error('subject') is-invalid @enderror" 
                                           id="floatingSubject" placeholder="Email Subject"
                                           value="{{ old('subject', 'Your Receipt #' . $sale->reference_number) }}" required>
                                    <label for="floatingSubject">Subject</label>
                                    @error('subject')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-floating">
                                    <textarea name="message" class="form-control" placeholder="Additional Message"
                                              id="floatingMessage" style="height: 100px">{{ old('message') }}</textarea>
                                    <label for="floatingMessage">Additional Message (Optional)</label>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-send me-1"></i> Send Receipt
                                </button>
                                <a href="{{ route('sales.receipt', $sale->id) }}" class="btn btn-secondary ms-2">
                                    <i class="bi bi-x-circle me-1"></i> Cancel
                                </a>
                            </div>
                        </form><!-- End Floating Labels Form -->
                        
                        <!-- Preview Card -->
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Email Preview</h5>
                            </div>
                            <div class="card-body">
                                <div class="border p-3 bg-light">
                                    <h6><strong>Subject:</strong> <span id="subjectPreview">{{ old('subject', 'Your Receipt #' . $sale->reference_number) }}</span></h6>
                                    <hr>
                                    <p><strong>To:</strong> <span id="emailPreview">{{ old('email', $sale->customer->email) }}</span></p>
                                    @if(old('message'))
                                    <p><strong>Message:</strong></p>
                                    <p id="messagePreview">{{ old('message') }}</p>
                                    <hr>
                                    @endif
                                    <p class="mb-1"><strong>Receipt Details:</strong></p>
                                    <ul class="mb-1">
                                        <li>Receipt #: {{ $sale->reference_number }}</li>
                                        <li>Date: {{ $sale->sale_date->format('M j, Y') }}</li>
                                        <li>Customer: {{ $sale->customer->name }}</li>
                                        <li>Total: {{ number_format($sale->total_price, 2) }} RWF</li>
                                    </ul>
                                    <p class="text-muted small mb-0">This is a preview of how the email will look.</p>
                                </div>
                            </div>
                        </div><!-- End Preview Card -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->

@include('theme.footer')

@push('scripts')
<script>
    // Live preview update
    document.addEventListener('DOMContentLoaded', function() {
        const emailInput = document.getElementById('floatingEmail');
        const subjectInput = document.getElementById('floatingSubject');
        const messageInput = document.getElementById('floatingMessage');
        
        const emailPreview = document.getElementById('emailPreview');
        const subjectPreview = document.getElementById('subjectPreview');
        const messagePreview = document.getElementById('messagePreview');
        
        emailInput.addEventListener('input', function() {
            emailPreview.textContent = this.value;
        });
        
        subjectInput.addEventListener('input', function() {
            subjectPreview.textContent = this.value;
        });
        
        messageInput.addEventListener('input', function() {
            messagePreview.textContent = this.value;
            if(this.value) {
                messagePreview.parentElement.style.display = 'block';
            } else {
                messagePreview.parentElement.style.display = 'none';
            }
        });
    });
</script>
@endpush