@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
    <!-- Page title and Add button -->
    <div class="pagetitle d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-people"></i> Employee List</h1>
        <a href="{{ route('employees.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Add New
        </a>
    </div>

    @include('theme.success')

    
    <!-- Employee Type Summary Cards -->
    <div class="row g-3 mb-4">
        <!-- Total Employees -->
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 text-center">
                <div class="card-body">
                    <i class="bi bi-people-fill fs-2 text-success mb-2"></i>
                    <h6 class="text-muted mb-1">Total Employees</h6>
                    <h3 class="fw-bold">{{ $employees->total() }}</h3>
                </div>
            </div>
        </div>

        <!-- Employee Type Counts -->
        @forelse($employeeTypeCounts as $typeCount)
            <div class="col-md-3">
                <div class="card shadow-sm border-0 h-100 text-center">
                    <div class="card-body">
                        <i class="bi bi-person-badge fs-2 text-info mb-2"></i>
                        <h6 class="text-muted mb-1">{{ $typeCount->employeeType->name ?? 'Unknown' }}</h6>
                        <h3 class="fw-bold">{{ $typeCount->total }}</h3>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="alert alert-info">No employee type data available.</div>
            </div>
        @endforelse
    </div>
    <!-- Employees Table -->
    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body p-4">
            <h5 class="card-title text-primary mb-3"><i class="bi bi-table"></i> All Employees</h5>

            <div class="table-responsive">
                <table id="employeeTable" class="table table-hover align-middle table-striped table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Photo</th>
                            <th>Name</th>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Gender</th>
                            <th>Type</th>
                            <th>Salary</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $key => $employee)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>
                                    <img src="{{ $employee->avatar ? asset('storage/' . $employee->avatar) : asset('images/default-avatar.png') }}" 
                                         alt="Avatar" class="rounded-circle" width="40">
                                </td>
                                <td><strong>{{ $employee->name }}</strong></td>
                                <td>{{ $employee->id_number }}</td>
                                <td>{{ $employee->email }}</td>
                                <td>{{ $employee->phone }}</td>
                                <td>
                                    <span class="badge bg-{{ $employee->gender === 'Male' ? 'primary' : 'info' }}">
                                        {{ $employee->gender }}
                                    </span>
                                </td>
                                <td>{{ $employee->employeeType->name ?? '-' }}</td>
                                <td>{{ $employee->salaryType->name ?? '-' }}</td>
                                <td>
                                    <small>{{ $employee->province }}, {{ $employee->district }}<br>
                                    {{ $employee->sector }}, {{ $employee->cell }}, {{ $employee->village }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $employee->active ? 'success' : 'secondary' }}">
                                        {{ $employee->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-outline-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to deactivate this employee?')" title="Deactivate">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-secondary">No employees found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="mt-3">
                {{ $employees->links() }}
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

<script>
$(document).ready(function() {
    $('#employeeTable').DataTable({
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'pB>>",
 buttons: [
    {
        extend: 'print',
        className: 'btn btn-sm btn-success', // solid green background
        text: '<i class="bi bi-printer"></i> Print'
    }
],


        responsive: true,
        pageLength: 10,
        lengthMenu: [[10,25,50,100,-1],[10,25,50,100,"All"]],
        columnDefs: [
            { orderable: false, targets: [1, 11] },
            { searchable: false, targets: [1, 11] }
        ],
        language: { search: "_INPUT_", searchPlaceholder: "Search employees..." }
    });

    // Bootstrap tooltip
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>

<style>
.dt-buttons .btn {
    margin-right: 5px;
    margin-bottom: 10px;
}
</style>


<!-- Optional DataTables styling -->
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
