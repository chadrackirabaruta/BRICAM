<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@include('theme.head')
<body>
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
    <!-- Page title and Add button -->
    <div class="pagetitle d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-person-lines-fill"></i> Customer List</h1>
        <a href="{{ route('customers.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Add New
        </a>
    </div>

    @include('theme.success')

    <!-- Customer Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Customers -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-people-fill fs-2 text-primary mb-2"></i>
                    <h6 class="text-muted mb-1">Total Customers</h6>
                    <h3 class="fw-bold">{{ $customers->count() }}</h3>
                </div>
            </div>
        </div>

        <!-- Customers by Type -->
        @forelse($customerTypeCounts as $type => $count)
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100 text-center">
                    <div class="card-body">
                        <i class="bi bi-person-badge fs-2 text-info mb-2"></i>
                        <h6 class="text-muted mb-1">{{ ucfirst($type) }}</h6>
                        <h3 class="fw-bold">{{ $count }}</h3>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">No customer type data available.</div>
            </div>
        @endforelse
    </div>

    <!-- Customers Table -->
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body p-4">
            <h5 class="card-title text-primary mb-3"><i class="bi bi-table"></i> All Customers</h5>

            <div class="table-responsive">
                <table id="customerTable" class="table table-hover align-middle table-striped table-bordered">
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
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <img src="{{ $customer->avatar ? asset('storage/' . $customer->avatar) : asset('images/default-avatar.png') }}" 
                                         alt="Avatar" class="rounded-circle" width="40">
                                </td>
                                <td><strong>{{ $customer->name }}</strong></td>
                                <td>{{ $customer->id_number ?? '-' }}</td>
                                <td>
                                    <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                                    @if($customer->email)<br><small class="text-muted">{{ $customer->email }}</small>@endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ strtolower($customer->customer_type) == 'wholesale' ? 'success' : (strtolower($customer->customer_type) == 'contractor' ? 'info' : 'primary') }}">
                                        {{ $customer->customer_type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ strtolower($customer->status) == 'active' ? 'success' : (strtolower($customer->status) == 'inactive' ? 'secondary' : (strtolower($customer->status) == 'pending' ? 'warning' : 'danger')) }}">
                                        {{ ucfirst($customer->status ?? 'Not Set') }}
                                    </span>
                                </td>
                                <td><small>{{ $customer->district ?? 'Unknown' }}</small></td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to deactivate this customer?')" data-bs-toggle="tooltip" title="Deactivate">
                                                <i class="bi bi-person-x"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-secondary">No customers found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

@include('theme.footer')

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<!-- jQuery & DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function() {
    $('#customerTable').DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'pB>>",
      buttons: [
    { 
        extend: 'print', 
        className: 'btn btn-sm btn-success', // solid green button
        text: '<i class="bi bi-printer"></i> Print', 
        titleAttr: 'Print' 
    }
]
,
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10,25,50,100,-1],[10,25,50,100,"All"]],
        columnDefs: [
            { orderable: false, targets: [1, 8] },
            { searchable: false, targets: [1, 8] }
        ],
        language: { search: "_INPUT_", searchPlaceholder: "Search customers..." }
    });

    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>

<style>
.dt-buttons .btn { margin-right: 5px; margin-bottom: 10px; }
.dataTables_wrapper .dataTables_filter input { border: 1px solid #dee2e6; padding: 0.375rem 0.75rem; border-radius: 0.25rem; margin-left: 0.5rem; }
.dataTables_wrapper .dataTables_length select { border: 1px solid #dee2e6; border-radius: 0.25rem; padding: 0.375rem 1.75rem 0.375rem 0.75rem; }
.dataTables_wrapper .dataTables_paginate .paginate_button { padding: 0.3em 0.8em; margin-left: 0.2em; border: 1px solid #dee2e6; }
.dataTables_wrapper .dataTables_paginate .paginate_button.current { background: #0d6efd; color: white !important; border: 1px solid #0d6efd; }
.badge { font-size: 0.85rem; padding: 0.35em 0.6em; }
</style>
