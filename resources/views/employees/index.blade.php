@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <!-- Page title and add button -->
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1><i class="bi bi-people"></i> Employee List</h1>
    <a href="{{ route('employees.create') }}" class="btn btn-success">
      <i class="bi bi-plus-circle"></i> Add New
    </a>
  </div>
  <!-- Success message -->
  @include('theme.success')

  <section class="section">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <h5 class="card-title text-primary mb-3"><i class="bi bi-table"></i> All Employees</h5>

        <div class="table-responsive">
          <table id="employeeTable" class="table table-hover align-middle table-striped">
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
                    <small>{{ $employee->province }}, {{ $employee->district }}<br>{{ $employee->sector }}, {{ $employee->cell }}, {{ $employee->village }}</small>
                  </td>
                  <td>
                    <span class="badge bg-{{ $employee->active ? 'success' : 'secondary' }}">
                      {{ $employee->active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="text-end">
                    <!-- View Button -->
                    <a href="{{ route('employees.show', $employee->id) }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="View Details">
                      <i class="bi bi-eye"></i>
                    </a>

                    <!-- Edit Button -->
                    <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Edit Employee">
                      <i class="bi bi-pencil"></i>
                    </a>

                    <!-- Delete Button -->
                    <form action="{{ route('employees.destroy', $employee->id) }}" method="POST" class="d-inline">
                      @csrf
                      @method('DELETE')
                      <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to deactivate this employee?')" data-bs-toggle="tooltip" title="Deactivate">
                        <i class="bi bi-trash"></i>
                      </button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="12" class="text-center">No employees found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </section>
</main>

<!-- Add these in your layout file's head section -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

<!-- Add these right before your closing </body> tag -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>

<script>
$(document).ready(function() {
    $('#employeeTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print', 'colvis'
        ],
        responsive: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        columnDefs: [
            { orderable: false, targets: [1, 11] }, // Disable sorting for photo and actions columns
            { searchable: false, targets: [1, 11] } // Disable searching for photo and actions columns
        ],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
        }
    });
    
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
</script>

<style>
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #dee2e6;
    padding: 0.375rem 0.75rem;
    border-radius: 0.25rem;
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
</style>

<!-- Scripts -->
@push('scripts')
  <!-- Simple DataTables -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const table = new simpleDatatables.DataTable("#employeeTable", {
        searchable: true,
        fixedHeight: true,
        perPage: 10
      });

      // Enable Bootstrap tooltips
      const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
      const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
      })
    });
  </script>
@endpush

@include('theme.footer')