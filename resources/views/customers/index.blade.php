@php
// Get unique districts from existing customers
$districts = App\Models\Customer::select('district')
    ->whereNotNull('district')
    ->distinct()
    ->orderBy('district')
    ->pluck('district');
@endphp

@include('theme.head')
@include('theme.header')
@include('theme.sidebar')
<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1><i class="bi bi-person-lines-fill"></i> Customer List</h1>
    <a href="{{ route('customers.create') }}" class="btn btn-success">
      <i class="bi bi-plus-circle"></i> Add New Customer
    </a>
  </div>

  @include('theme.success')
<section class="section">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <h5 class="card-title text-primary mb-3"><i class="bi bi-table"></i> Customer Records</h5>

        <!-- Filter Row -->
        <div class="row mb-3">
          <div class="col-md-3">
            <select id="typeFilter" class="form-select">
              <option value="">All Customer Types</option>
              <option value="Retail">Retail</option>
              <option value="Wholesale">Wholesale</option>
              <option value="Contractor">Contractor</option>
            </select>
          </div>
          <div class="col-md-3">
            <select id="statusFilter" class="form-select">
              <option value="">All Statuses</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="banned">Banned</option>
            </select>
          </div>
          <div class="col-md-3">
            <select id="locationFilter" class="form-select">
              <option value="">All Districts</option>
              @foreach($districts as $district)
                <option value="{{ $district }}">{{ $district }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-3">
            <button id="resetFilters" class="btn btn-outline-secondary w-100">
              <i class="bi bi-arrow-counterclockwise"></i> Reset Filters
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table id="customerTable" class="table table-hover align-middle table-striped">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Photo</th>
                <th>Name</th>
                <th>ID Number</th>
                <th>Contact</th>
                <th>Type</th>
                <th>Status</th>
                <th>Location</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($customers as $key => $customer)
                <tr>
                  <td>{{ $loop->iteration }}</td>
                  <td>
                    <img src="{{ $customer->avatar ? asset('storage/' . $customer->avatar) : asset('images/default-avatar.png') }}"
                      alt="Avatar" class="rounded-circle" width="40">
                  </td>
                  <td>
                    <strong>{{ $customer->name }}</strong>
                  </td>
                  <td>
                    @if($customer->id_number)
                      <small class="text-muted">{{ $customer->id_number }}</small>
                    @else
                      <span class="text-muted">N/A</span>
                    @endif
                  </td>
                  <td>
                    <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                    @if($customer->email)
                      <br><small class="text-muted">{{ $customer->email }}</small>
                    @endif
                  </td>
                  <td>
                    <span class="badge 
                      @if($customer->customer_type == 'Wholesale') bg-success
                      @elseif($customer->customer_type == 'Contractor') bg-info
                      @else bg-primary @endif">
                      {{ $customer->customer_type }}
                    </span>
                  </td>
                  <td>
                    @if($customer->status)
                      <span class="badge 
                        @if($customer->status === 'active') bg-success
                        @elseif($customer->status === 'inactive') bg-warning
                        @elseif($customer->status === 'banned') bg-danger
                        @endif">
                        {{ ucfirst($customer->status) }}
                      </span>
                    @else
                      <span class="badge bg-secondary">Not Set</span>
                    @endif
                  </td>
                  <td>
                    <small>{{ $customer->district ?? 'Unknown' }}</small>
                  </td>
                  <td class="text-end">
                    <div class="btn-group" role="group">
                      <a href="{{ route('customers.show', $customer->id) }}" 
                         class="btn btn-sm btn-outline-info me-2 d-flex align-items-center justify-content-center" 
                         data-bs-toggle="tooltip" title="View" style="width: 32px; height: 32px;">
                        <i class="bi bi-eye"></i>
                      </a>
                      <a href="{{ route('customers.edit', $customer->id) }}" 
                         class="btn btn-sm btn-outline-primary me-2 d-flex align-items-center justify-content-center" 
                         data-bs-toggle="tooltip" title="Edit" style="width: 32px; height: 32px;">
                        <i class="bi bi-pencil"></i>
                      </a>
                      <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center" 
                                onclick="return confirm('Are you sure you want to deactivate this customer?')" 
                                data-bs-toggle="tooltip" title="Deactivate" style="width: 32px; height: 32px;">
                          <i class="bi bi-person-x"></i>
                        </button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="9" class="text-center py-4">No customers found. <a href="{{ route('customers.create') }}">Create your first customer</a></td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- DataTables Assets -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize DataTable
    var table = $('#customerTable').DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'pB>>",
        buttons: [
            {
                extend: 'copy',
                className: 'btn btn-sm btn-outline-secondary',
                text: '<i class="bi bi-clipboard"></i> Copy'
            },
            {
                extend: 'excel',
                className: 'btn btn-sm btn-outline-success',
                text: '<i class="bi bi-file-excel"></i> Excel'
            },
            {
                extend: 'pdf',
                className: 'btn btn-sm btn-outline-danger',
                text: '<i class="bi bi-file-pdf"></i> PDF'
            },
            {
                extend: 'print',
                className: 'btn btn-sm btn-outline-info',
                text: '<i class="bi bi-printer"></i> Print'
            },
            {
                extend: 'colvis',
                className: 'btn btn-sm btn-outline-primary',
                text: '<i class="bi bi-eye-slash"></i> Columns'
            }
        ],
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        columnDefs: [
            { orderable: false, targets: [1, 8] }, // Disable sorting for photo and actions columns
            { searchable: false, targets: [1, 8] } // Disable searching for photo and actions columns
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search customers...",
        }
    });

    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Filter by customer type
    $('#typeFilter').on('change', function() {
        table.column(5).search(this.value).draw();
    });

    // Filter by status
    $('#statusFilter').on('change', function() {
        table.column(6).search(this.value).draw();
    });

    // Filter by location
    $('#locationFilter').on('change', function() {
        table.column(7).search(this.value).draw();
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#typeFilter, #statusFilter, #locationFilter').val('');
        table.columns().search('').draw();
    });
});
</script>

<style>
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #dee2e6;
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;
    margin-left: 0.5rem;
}

.dataTables_wrapper .dataTables_length select {
    border: 1px solid #dee2e6;
    border-radius: 0.25rem;
    padding: 0.375rem 1.75rem 0.375rem 0.75rem;
}

.dt-buttons .btn {
    margin-right: 5px;
    margin-bottom: 10px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.3em 0.8em;
    margin-left: 0.2em;
    border: 1px solid #dee2e6;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background: #0d6efd;
    color: white !important;
    border: 1px solid #0d6efd;
}
</style>

@include('theme.footer')