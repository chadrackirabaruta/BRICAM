<!-- resources/views/sales/_payment_form.blade.php -->
<div class="card mb-4">
    <div class="card-header">
        <h5>Record Payment</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('sales.payments.store', $sale->id) }}" method="POST">
            @csrf
            
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Amount</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="amount" class="form-control" 
                               step="0.01" min="0.01" max="{{ $sale->balance }}" required>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select" required>
                        <option value="cash">Cash</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Payment Date</label>
                    <input type="date" name="payment_date" class="form-control" 
                           value="{{ now()->format('Y-m-d') }}" required>
                </div>
                
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        Record Payment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>