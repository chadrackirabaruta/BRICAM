@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <!-- Title + Button with Modal -->
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1><i class="bi bi-bricks"></i> Production Records</h1>
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductionModal">
      <i class="bi bi-plus-circle"></i> Add Production
    </button>
  </div>
  @include('theme.success')

  <!-- Alert for Today's Records -->

@if(!request()->filled('employee_id') && !request()->filled('from_date') && !request()->filled('to_date'))
  <div class="alert alert-info d-flex align-items-center gap-2 small">
    <i class="bi bi-calendar-event fs-5 text-primary"></i>
    <span>
      <strong>Showing Today's Records:</strong>
      <span class="badge bg-primary-subtle text-primary fw-semibold">
        {{ now()->format('l, F j, Y') }}
      </span>
    </span>
  </div>
@endif
<!-- Alert for Filter Applied -->


 @if(request()->filled('employee_id') || request()->filled('from_date') || request()->filled('to_date'))
  <div class="alert alert-primary small d-flex align-items-center gap-2">
    <i class="bi bi-funnel-fill text-primary fs-5"></i>
    <div>
      <strong>Filter Applied:</strong>

      @if(request()->filled('employee_id'))
        <span class="badge bg-info text-dark">
          Employee: {{ $employees->firstWhere('id', request('employee_id'))?->name ?? 'Unknown' }}
        </span>
      @endif

      @if(request()->filled('from_date') && request()->filled('to_date'))
        <span class="badge bg-secondary">
          Dates: {{ \Carbon\Carbon::parse(request('from_date'))->format('M d, Y') }}
          &mdash;
          {{ \Carbon\Carbon::parse(request('to_date'))->format('M d, Y') }}
        </span>
      @elseif(request()->filled('from_date'))
        <span class="badge bg-secondary">
          From: {{ \Carbon\Carbon::parse(request('from_date'))->format('M d, Y') }}
        </span>
      @elseif(request()->filled('to_date'))
        <span class="badge bg-secondary">
          To: {{ \Carbon\Carbon::parse(request('to_date'))->format('M d, Y') }}
        </span>
      @endif
    </div>
  </div>
@endif


<!-- Filter Form -->
<form method="GET" class="row g-3 align-items-end mb-4">
  <!-- Employee Filter -->
  <div class="col-md-4">
    <label>Employee</label>
    <select name="employee_id" class="form-select">
      <option value="">-- All Employees --</option>
      @foreach($employees as $emp)
        <option value="{{ $emp->id }}" {{ request('employee_id') == $emp->id ? 'selected' : '' }}>
          {{ $emp->name }}
        </option>
      @endforeach
    </select>
  </div>

  <!-- Date From -->
  <div class="col-md-3">
    <label>From</label>
    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
  </div>

  <!-- Date To -->
  <div class="col-md-3">
    <label>To</label>
    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
  </div>

  <!-- Filter + Reset Buttons -->
  <div class="col-md-2">
    <label class="d-block invisible">Action</label> <!-- Keeps height aligned -->
    <div class="d-flex gap-2">
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-funnel"></i> Filter
      </button>
     <a href="{{ route('productions.index') }}" class="btn btn-outline-secondary d-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
  <i class="bi bi-x-circle fs-4"></i>
</a>

    </div>
  </div>
</form>



  <!-- Table body -->
  <section class="section">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <h5 class="card-title text-primary mb-3"><i class="bi bi-table"></i> All Productions</h5>
        <div class="table-responsive">
          <table id="productionTable" class="table table-hover align-middle table-striped">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Date</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($productions as $key => $production)
                <tr>
                  <td>{{ $key + 1 }}</td>
                  <td>{{ $production->employee->name }}</td>
                  <td>{{ $production->production_date }}</td>
                  <td>{{ $production->quantity }}</td>
                  <td>{{ number_format($production->unit_price) }} Frw</td>
                <td>{{ number_format($production->quantity * $production->unit_price) }} Frw</td>
                  <td class="text-end">
                   <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editProductionModal{{ $production->id }}" title="Edit Record">
                       <i class="bi bi-pencil"></i>
                    </button>
                    <form action="{{ route('productions.destroy', $production->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this?')" data-bs-toggle="tooltip" title="Delete">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td colspan="7" class="text-center text-secondary">No production records found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </section>
</main>
<!-- Bootstrap Modal: Add Production -->
<div class="modal fade" id="addProductionModal" tabindex="-1" aria-labelledby="addProductionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form action="{{ route('productions.store') }}" method="POST" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title" id="addProductionModalLabel">
          <i class="bi bi-plus-circle"></i> Add New Production
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div class="row g-3">
          <!-- Employee -->
          <div class="col-md-6">
            <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
            <select class="form-select" name="employee_id" required>
              <option value="">-- Select Employee --</option>
              @foreach($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
              @endforeach
            </select>
          </div>

          <!-- Date -->
          <div class="col-md-6">
            <label for="production_date" class="form-label">Date <span class="text-danger">*</span></label>
            <input type="date" name="production_date" class="form-control" value="{{ date('Y-m-d') }}" required>
          </div>

          <!-- Quantity -->
          <div class="col-md-6">
            <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
            <input type="number" name="quantity" class="form-control" min="1" required>
          </div>

          <!-- Price -->
          <div class="col-md-6">
            <label for="unit_price" class="form-label">Unit Price <span class="text-danger">*</span></label>
            <input type="number" step="0.01" name="unit_price" class="form-control" value="25" required>
          </div>

          <!-- Remarks -->
          <div class="col-md-12">
            <label for="remarks" class="form-label">Remarks (optional)</label>
            <textarea name="remarks" class="form-control" rows="2"></textarea>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="submit" class="btn btn-success">
          <i class="bi bi-check-circle"></i> Save
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

@include('theme.footer')

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const table = new simpleDatatables.DataTable("#productionTable", {
      searchable: true,
      fixedHeight: true,
      perPage: 10
    });

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // If form was submitted with errors, auto re-open modal
    @if ($errors->any())
      const failedModal = new bootstrap.Modal(document.getElementById('addProductionModal'));
      failedModal.show();
    @endif
  });
</script>
@endpush
<!-- Edit Production Modal -->
<!-- Loop through each production to create an edit modal -->


@foreach($productions as $production)
  <div class="modal fade" id="editProductionModal{{ $production->id }}" tabindex="-1" aria-labelledby="editProductionModalLabel{{ $production->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <form action="{{ route('productions.update', $production->id) }}" method="POST" class="modal-content">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="editProductionModalLabel{{ $production->id }}">
            <i class="bi bi-pencil"></i> Edit Production ({{ $production->employee->name }})
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">
            <!-- Employee -->
            <div class="col-md-6">
              <label class="form-label">Employee</label>
              <select name="employee_id" class="form-select" required>
                @foreach($employees as $employee)
                  <option value="{{ $employee->id }}" {{ $production->employee_id == $employee->id ? 'selected' : '' }}>
                    {{ $employee->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <!-- Date -->
            <div class="col-md-6">
              <label class="form-label">Production Date</label>
              <input type="date" name="production_date" class="form-control"
                     value="{{ $production->production_date }}" required>
            </div>

            <!-- Quantity -->
            <div class="col-md-6">
              <label class="form-label">Quantity</label>
              <input type="number" name="quantity" class="form-control"
                     value="{{ $production->quantity }}" min="1" required>
            </div>

            <!-- Unit Price -->
            <div class="col-md-6">
              <label class="form-label">Unit Price</label>
              <input type="number" step="0.01" name="unit_price" class="form-control"
                     value="{{ $production->unit_price }}" required>
            </div>

            <!-- Remarks -->
            <div class="col-md-12">
              <label class="form-label">Remarks</label>
              <textarea name="remarks" class="form-control" rows="2">{{ $production->remarks }}</textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check2-circle"></i> Update
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
@endforeach