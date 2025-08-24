@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1><i class="bi bi-plus-circle"></i> Add Production</h1>
    <a href="{{ route('production.index') }}" class="btn btn-secondary">
      <i class="bi bi-arrow-left-circle"></i> Back to List
    </a>
  </div>

  @include('theme.success')

  <section class="section">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <h5 class="card-title text-primary mb-3"><i class="bi bi-pencil-square"></i> Production Form</h5>

        <form action="{{ route('production.store') }}" method="POST">
          @csrf

          <div class="row g-3">
            <div class="col-md-6">
              <label for="employee_id" class="form-label"><i class="bi bi-person"></i> Employee</label>
              <select class="form-select" name="employee_id" required>
                <option value="">-- Select Employee --</option>
                @foreach($employees as $employee)
                  <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                    {{ $employee->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6">
              <label for="production_date" class="form-label"><i class="bi bi-calendar"></i> Production Date</label>
              <input type="date" name="production_date" class="form-control" value="{{ old('production_date', date('Y-m-d')) }}" required>
            </div>

            <div class="col-md-6">
              <label for="quantity" class="form-label"><i class="bi bi-box-seam"></i> Quantity (Bricks)</label>
              <input type="number" name="quantity" class="form-control" value="{{ old('quantity') }}" required min="1">
            </div>

            <div class="col-md-6">
              <label for="unit_price" class="form-label"><i class="bi bi-currency-exchange"></i> Unit Price (Frw / Tafari)</label>
              <input type="number" step="0.01" name="unit_price" class="form-control" value="{{ old('unit_price', 25) }}" required>
            </div>

            <div class="col-md-12">
              <label for="remarks" class="form-label"><i class="bi bi-chat-square-text"></i> Remarks (optional)</label>
              <textarea name="remarks" class="form-control" rows="3">{{ old('remarks') }}</textarea>
            </div>
          </div>

          <div class="text-end mt-4">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Save</button>
          </div>
        </form>

      </div>
    </div>
  </section>
</main>

@include('theme.footer')