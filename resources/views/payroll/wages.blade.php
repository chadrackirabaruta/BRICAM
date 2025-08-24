@include('theme.head')
@include('theme.header')
@include('theme.sidebar')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" />



<!-- Buttons Extension -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>


<!-- DataTable Initialization -->
<script>
$(document).ready(function () {
  $('.datatable-wages').DataTable({
    responsive: true,
    paging: true,
    ordering: true,
    searching: true,
    lengthChange: true,
    pageLength: 10,
    dom: 'Bfrtip',
    buttons: [
      {
        extend: 'copyHtml5',
        text: '<i class="bi bi-clipboard"></i> Copy',
        className: 'btn btn-sm',
        // Use custom background and text colors for standout button
        // A nice bright blue copy button
        init: function(api, node, config) {
          $(node).removeClass('btn-outline-secondary').addClass('btn-primary').css({
            'color': 'white',
            'border': 'none',
          });
        }
      },
      {
        extend: 'excelHtml5',
        text: '<i class="bi bi-file-earmark-excel"></i> Excel',
        className: 'btn btn-sm',
        // Rich green Excel button
        init: function(api, node, config) {
          $(node).removeClass('btn-outline-success').addClass('btn-success').css({
            'color': 'white',
            'border': 'none',
          });
        }
      },
      {
        extend: 'pdfHtml5',
        text: '<i class="bi bi-file-earmark-pdf"></i> PDF',
        className: 'btn btn-sm',
        // Strong red PDF button
        init: function(api, node, config) {
          $(node).removeClass('btn-outline-danger').addClass('btn-danger').css({
            'color': 'white',
            'border': 'none',
          });
        }
      },
      {
        extend: 'print',
        text: '<i class="bi bi-printer"></i> Print',
        className: 'btn btn-sm',
        // Deep blue print button
        init: function(api, node, config) {
          $(node).removeClass('btn-outline-primary').addClass('btn-info').css({
            'color': 'white',
            'border': 'none',
          });
        }
      }
    ]
  });
});



</script>
<style>
.dt-buttons {
  margin-bottom: 15px;
}
  
</style>


<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1><i class="bi bi-cash-stack"></i> Wages Management</h1>
    <div>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWageModal">
        <i class="bi bi-plus-circle"></i> Add New
      </button>
      <a href="{{ route('payroll.wages.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-arrow-clockwise"></i> Refresh
      </a>
    </div>
  </div>

  <section class="section">
    <div class="card p-3 shadow-sm">

      {{-- Flash messages --}}
     
  @include('theme.success')

      {{-- Filters --}}
      <form method="GET" action="{{ route('payroll.wages.index') }}" class="row g-2 mb-3">
        <div class="col-md-3">
          <input type="text" name="search" class="form-control" placeholder="🔍 Search employee..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
          <select name="employee_type" class="form-select">
           
            @foreach ($employeeTypes as $id => $type)
              <option value="{{ $id }}" {{ request('employee_type') == $id ? 'selected' : (request('employee_type') === null && $id == 3 ? 'selected' : '') }}>
                {{ ucfirst($type) }}
              </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <select name="month" class="form-select">
            <option value="">All Months</option>
            @for ($m = 1; $m <= 12; $m++)
              <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                {{ \Carbon\Carbon::create()->month($m)->format('F') }}
              </option>
            @endfor
          </select>
        </div>
        <div class="col-md-2">
          <select name="year" class="form-select">
            <option value="">All Years</option>
            @foreach ($years as $year)
              <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-1">
          <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i></button>
        </div>
      </form>

      {{-- Wages Table --}}
     <div class="table-responsive">
  <table class="table table-hover table-bordered datatable-wages text-center align-middle interactive-table">
    <thead class="table-primary text-white">
            <tr>
              <th>#</th>
              <th><i class="bi bi-person"></i> Employee</th>
              <th><i class="bi bi-person-badge"></i> Type</th>
              <th><i class="bi bi-calendar-event"></i> Date</th>
              <th><i class="bi bi-cash-coin"></i> Amount</th>
              <th><i class="bi bi-stickies"></i> Notes</th>
              <th><i class="bi bi-tools"></i> Actions</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($wages as $index => $wage)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $wage->employee->name ?? 'N/A' }}</td>
                <td><span class="badge bg-info text-capitalize">{{ $wage->employee_type }}</span></td>
                <td>{{ \Carbon\Carbon::parse($wage->date)->format('d M Y') }}</td>
                <td>{{ number_format($wage->amount, 0) }} RWF</td>
                <td>{{ Str::limit($wage->notes, 20) ?? '-' }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-primary view-btn" data-wage="{{ json_encode($wage) }}">
                    <i class="bi bi-eye-fill"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-warning edit-btn" data-wage="{{ json_encode($wage) }}">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <form action="{{ route('payroll.wages.destroy', $wage->id) }}" method="POST" class="d-inline"
                        onsubmit="return confirm('Delete this wage?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                      <i class="bi bi-trash-fill"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </section>
</main>

<!-- View Wage Modal -->
<div class="modal fade" id="viewWageModal" tabindex="-1" aria-labelledby="viewWageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="viewWageModalLabel">Wage Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row mb-3">
          <div class="col-md-4 fw-bold">Employee:</div>
          <div class="col-md-8" id="view-employee"></div>
        </div>
        <div class="row mb-3">
          <div class="col-md-4 fw-bold">Type:</div>
          <div class="col-md-8" id="view-type"></div>
        </div>
        <div class="row mb-3">
          <div class="col-md-4 fw-bold">Date:</div>
          <div class="col-md-8" id="view-date"></div>
        </div>
        <div class="row mb-3">
          <div class="col-md-4 fw-bold">Amount:</div>
          <div class="col-md-8" id="view-amount"></div>
        </div>
        <div class="row mb-3">
          <div class="col-md-4 fw-bold">Notes:</div>
          <div class="col-md-8" id="view-notes"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Add Wage Modal -->
<div class="modal fade" id="addWageModal" tabindex="-1" aria-labelledby="addWageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addWageModalLabel">Add New Wage</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
    <form id="addWageForm" action="{{ route('payroll.wages.store') }}" method="POST">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label for="add-employee-id" class="form-label">Employee</label>
            <select class="form-select" id="add-employee-id" name="employee_id" required>
              <option value="">Select Employee</option>
              @foreach($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="add-employee-type" class="form-label">Employee Type</label>
       <select class="form-select" id="add-employee-type" name="employee_type" required>
    <option value="">Select Type</option>
    @foreach($employeeTypes as $id => $type)
        <option value="{{ $id }}" {{ $id == $defaultEmployeeTypeId ? 'selected' : '' }}>
            {{ ucfirst($type) }}
        </option>
    @endforeach
</select>

          </div>
          <div class="mb-3">
            <label for="add-date" class="form-label">Date</label>
            <input type="date" class="form-control" id="add-date" name="date" required>
          </div>
          <div class="mb-3">
            <label for="add-amount" class="form-label">Amount (RWF)</label>
            <input type="number" class="form-control" id="add-amount" name="amount" required>
          </div>
          <div class="mb-3">
            <label for="add-notes" class="form-label">Notes</label>
            <textarea class="form-control" id="add-notes" name="notes" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Wage</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Wage Modal -->
<div class="modal fade" id="editWageModal" tabindex="-1" aria-labelledby="editWageModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content shadow">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title" id="editWageModalLabel">Edit Wage</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="editWageForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-body">
          <div class="mb-3">
            <label for="edit-employee-id" class="form-label">Employee</label>
            <select class="form-select" id="edit-employee-id" name="employee_id" required>
              <option value="">Select Employee</option>
              @foreach($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="edit-employee-type" class="form-label">Employee Type</label>
            <select class="form-select" id="edit-employee-type" name="employee_type" required>
              <option value="">Select Type</option>
              @foreach($employeeTypes as $type)
                <option value="{{ $type }}">{{ ucfirst($type) }}</option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="edit-date" class="form-label">Date</label>
            <input type="date" class="form-control" id="edit-date" name="date" required>
          </div>
          <div class="mb-3">
            <label for="edit-amount" class="form-label">Amount (RWF)</label>
            <input type="number" class="form-control" id="edit-amount" name="amount" required>
          </div>
          <div class="mb-3">
            <label for="edit-notes" class="form-label">Notes</label>
            <textarea class="form-control" id="edit-notes" name="notes" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning text-white">Update Wage</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Required Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // View button handler
    document.querySelectorAll('.view-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const wage = JSON.parse(this.dataset.wage);
        document.getElementById('view-employee').innerText = wage.employee?.name || 'N/A';
        document.getElementById('view-type').innerHTML = `<span class="badge bg-info">${wage.employee_type}</span>`;
        document.getElementById('view-date').innerText = new Date(wage.date).toLocaleDateString();
        document.getElementById('view-amount').innerText = new Intl.NumberFormat().format(wage.amount) + ' RWF';
        document.getElementById('view-notes').innerText = wage.notes || 'N/A';
        
        new bootstrap.Modal(document.getElementById('viewWageModal')).show();
      });
    });

    // Edit button handler
    document.querySelectorAll('.edit-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        const wage = JSON.parse(this.dataset.wage);
        document.getElementById('editWageForm').action = `/payroll/wages/${wage.id}`;
        document.getElementById('edit-employee-id').value = wage.employee_id;
        document.getElementById('edit-employee-type').value = wage.employee_type;
        document.getElementById('edit-date').value = wage.date.split('T')[0];
        document.getElementById('edit-amount').value = wage.amount;
        document.getElementById('edit-notes').value = wage.notes || '';

        new bootstrap.Modal(document.getElementById('editWageModal')).show();
      });
    });

    // Set today's date in the add form
    document.getElementById('add-date').value = new Date().toISOString().split('T')[0];
  });
</script>
