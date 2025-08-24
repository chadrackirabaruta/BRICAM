@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1><i class="bi bi-truck"></i> Transport Records</h1>
  </div>

  @include('theme.success')

  {{-- FORM --}}
  <form action="{{ route('transport-records.store.bulk') }}" method="POST" class="card p-4 mt-3">
    @csrf
    <div class="row mb-3">
      <div class="col-md-6">
        <label><strong>Umukozi</strong> <span class="text-danger">*</span></label>
   <select name="employee_id" class="form-select" required>
    <option value="">-- Hitamo Umukozi --</option>
    @forelse($employees->where('active', 1) as $employee)
        <option value="{{ $employee->id }}"
                {{ (request('employee_id') == $employee->id || old('employee_id') == $employee->id) ? 'selected' : '' }}>
            {{ $employee->name }}
        </option>
    @empty
        <option value="" disabled>-- Nta mukozi usanzwe asabwe --</option>
    @endforelse
</select>
      </div>
      <div class="col-md-6">
        <label><strong>Itariki</strong> <span class="text-danger">*</span></label>
        <input type="date" name="transport_date" value="{{ request('date') ?? now()->toDateString() }}" class="form-control" required>
      </div>
    </div>

    <hr>
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>Icyakozwe</th>
            <th>Status (amatafari)</th>
            <th>Umubare</th>
            <th>Aho byakorewe</th>
            <th>Gukuraho</th>
          </tr>
        </thead>
        <tbody id="rowsContainer"></tbody>
      </table>
    </div>

    <button type="button" id="addRowBtn" class="btn btn-outline-primary btn-sm mb-3">
      <i class="bi bi-plus-circle"></i> Andika Igikorwa Gishya
    </button>

    <button type="submit" class="btn btn-success w-100">
      <i class="bi bi-save"></i> Bika Ibyose
    </button>
  </form>



  {{-- ALL EMPLOYEES SUMMARY --}}
<hr class="my-4">
<div class="card p-3 p-md-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-column flex-md-row gap-2 gap-md-0">
    <h5 class="mb-0">👥 Ibyakozwe n'abakozi bose</h5>
    <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-end">
      <!-- Employee Filter -->
      <div class="dropdown">
        <button class="btn btn-sm btn-outline-primary dropdown-toggle d-flex align-items-center" type="button" id="employeeFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-people-fill me-1"></i> 
          @if(!empty(request('employee_filter')) && $employees->where('id', request('employee_filter'))->isNotEmpty())
            {{ $employees->firstWhere('id', request('employee_filter'))->name }}
          @else
            Abakozi bose
          @endif
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="employeeFilterDropdown">
          <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('transport-records.index', request()->except(['employee_filter', 'page'])) }}">
            Abakozi bose
            @if(!request('employee_filter'))
              <span class="badge bg-primary ms-2">✓</span>
            @endif
          </a></li>
          @foreach($employees as $emp)
            <li><a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('transport-records.index', array_merge(request()->except(['employee_filter', 'page']), ['employee_filter' => $emp->id])) }}">
              {{ $emp->name }}
              @if(request('employee_filter') == $emp->id)
                <span class="badge bg-primary ms-2">✓</span>
              @endif
            </a></li>
          @endforeach
        </ul>
      </div>
      
      <!-- Date Filter -->
      <div class="dropdown">
        <button class="btn btn-sm btn-outline-primary dropdown-toggle d-flex align-items-center" type="button" id="dateRangeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-calendar-range me-1"></i> 
          @php
            $dateLabel = 'Itariki';
            if (request('date_range') === 'custom' && !empty(request('start_date')) && !empty(request('end_date'))) {
                $dateLabel = date('d/m/Y', strtotime(request('start_date'))) . ' - ' . date('d/m/Y', strtotime(request('end_date')));
            } elseif (in_array(request('filter'), ['today', 'week', 'month', 'year'])) {
                $dateLabel = trans('filter.' . request('filter')); // Add translations in your language files
            }
          @endphp
          {{ $dateLabel }}
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dateRangeDropdown">
          <li>
            <a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#dateRangeModal">
              <i class="bi bi-calendar3 me-2"></i> Hitamo itariki...
            </a>
          </li>
          <li><hr class="dropdown-divider"></li>
          @foreach(['today' => 'Uyu munsi', 'week' => 'Icyi cyumweru', 'month' => 'Uku kwezi', 'year' => 'Uyu mwaka'] as $filter => $label)
            <li>
              <a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('transport-records.index', array_merge(request()->except(['filter', 'date_range', 'start_date', 'end_date', 'page']), ['filter' => $filter])) }}">
                <span><i class="bi bi-calendar-{{ $filter === 'month' ? 'month' : ($filter === 'week' ? 'week' : '') }} me-2"></i> {{ $label }}</span>
                @if(request('filter') == $filter && !request('date_range')))
                  <span class="badge bg-primary">✓</span>
                @endif
              </a>
            </li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Date Range Modal -->
<div class="modal fade" id="dateRangeModal" tabindex="-1" aria-labelledby="dateRangeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dateRangeModalLabel">Hitamo itariki</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="dateRangeForm" action="{{ route('transport-records.index') }}" method="GET">
          @foreach(request()->except(['date_range', 'start_date', 'end_date', 'page']) as $key => $value)
            @if(is_array($value))
              @foreach($value as $item)
                @if(!is_array($item))
                  <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                @endif
              @endforeach
            @else
              <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
          @endforeach
          
          <input type="hidden" name="date_range" value="custom">
          
          <div class="mb-3">
            <label for="start_date" class="form-label">Kuva</label>
            <input type="date" class="form-control" id="start_date" name="start_date" 
                   value="{{ request('start_date', now()->subMonth()->format('Y-m-d')) }}" required>
          </div>
          <div class="mb-3">
            <label for="end_date" class="form-label">Kugeza</label>
            <input type="date" class="form-control" id="end_date" name="end_date" 
                   value="{{ request('end_date', now()->format('Y-m-d')) }}" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Guhagarika</button>
        <button type="submit" form="dateRangeForm" class="btn btn-primary">Reba</button>
      </div>
    </div>
  </div>
</div>
        
        @if(request('filter') || request('date_range') || request('employee_filter'))
          <a href="{{ route('transport-records.index') }}" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-x-circle"></i> Subiza
          </a>
        @endif
      </div>
    </div>
    
 @php
    $employee = request('employee_filter') 
        ? \App\Models\Employee::find(request('employee_filter'))?->name 
        : null;

    $range = explode(' - ', request('date_range'));
    $hasCustomRange = count($range) === 2;
    $start = $hasCustomRange ? \Carbon\Carbon::parse($range[0])->format('d/m/Y') : null;
    $end = $hasCustomRange ? \Carbon\Carbon::parse($range[1])->format('d/m/Y') : null;
@endphp

<div class="alert alert-info py-2 mb-3 d-flex align-items-center">
  <i class="bi bi-info-circle me-2"></i>

  @if($employee)
    Reba ibyakozwe na <strong>{{ $employee }}</strong>
  @else
    Reba ibyakozwe n'<strong>abakozi bose</strong>
  @endif

  &nbsp;—&nbsp;

  @if($hasCustomRange)
    <strong>kuva {{ $start }} kugeza {{ $end }}</strong>
  @elseif(request('filter') === 'today')
    <strong>uyu munsi</strong> ({{ now()->format('d/m/Y') }})
  @elseif(request('filter') === 'week')
    <strong>iyi cyumweru</strong> ({{ now()->startOfWeek()->format('d/m/Y') }} - {{ now()->endOfWeek()->format('d/m/Y') }})
  @elseif(request('filter') === 'month')
    <strong>uyu kwezi</strong> ({{ now()->startOfMonth()->format('d/m/Y') }} - {{ now()->endOfMonth()->format('d/m/Y') }})
  @elseif(request('filter') === 'year')
    <strong>uyu mwaka</strong> ({{ now()->startOfYear()->format('d/m/Y') }} - {{ now()->endOfYear()->format('d/m/Y') }})
  @else
    ku itariki <strong>{{ \Carbon\Carbon::parse($date ?? now())->format('d/m/Y') }}</strong>
  @endif
</div>

    
    <div class="table-responsive">
      <table class="table table-bordered text-center">
        <thead class="table-light align-middle">
          <tr>
            <th rowspan="2">Umukozi</th>
            @foreach($categories as $cat)
              <th colspan="2">{{ $cat->name }}</th>
            @endforeach
            <th rowspan="2">TOTAL (Rwf)</th>
            <th rowspan="2">Ibikorwa</th>
          </tr>
        </thead>
        <tbody>
          @forelse($summaryPerEmployee as $employeeName => $records)
          <tr>
            <td>{{ $employeeName }}</td>
            @php
              $catMap = $records->keyBy('transport_category_id');
              $rowTotal = 0;
            @endphp
            @foreach($categories as $cat)
              @php
                $r = $catMap->get($cat->id);
                $qty = $r ? $r->total_qty : 0;
                $price = $r ? $r->total_price : 0;
                $rowTotal += $price;
              @endphp
              <td>{{ number_format($qty) }}</td>
              <td>{{ number_format($price) }}</td>
            @endforeach
            <td class="fw-bold">{{ number_format($rowTotal) }} Rwf</td>
            <td>
           @if($records->first()?->employee_id)

<form method="GET" action="{{ route('transport-records.show', ['employeeId' => $records->first()->employee_id]) }}">
    {{-- Only pass one type of date filter --}}
    @if(request('time_filter'))
        <input type="hidden" name="time_filter" value="{{ request('time_filter') }}">
    @elseif(request('filter'))
        <input type="hidden" name="filter" value="{{ request('filter') }}">
    @elseif(request('start_date') && request('end_date'))
        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
    @endif
    
    {{-- Always pass these filters --}}
    @if(request('employee_filter'))
        <input type="hidden" name="employee_filter" value="{{ request('employee_filter') }}">
    @endif

    <button type="submit" class="btn btn-outline-info btn-sm">
        <i class="bi bi-eye"></i> Reba
    </button>
</form>



@endif

            </td>
          </tr>
          @empty
          <tr>
            <td colspan="{{ 2 * count($categories) + 2 }}" class="text-center">
              <em>Nta bikorwa byabonetse kuri iyi tariki.</em>
            </td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- Date Range Modal -->
<div class="modal fade" id="dateRangeModal" tabindex="-1" aria-labelledby="dateRangeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="dateRangeModalLabel">Hitamo itariki</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="GET" action="{{ route('transport-records.index') }}">
        <div class="modal-body">
          <input type="hidden" name="employee_filter" value="{{ request('employee_filter') }}">
          <div class="mb-3">
            <label for="startDate" class="form-label">Kuva:</label>
            <input type="date" class="form-control" id="startDate" name="start_date" required>
          </div>
          <div class="mb-3">
            <label for="endDate" class="form-label">Kugeza:</label>
            <input type="date" class="form-control" id="endDate" name="end_date" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Reba</button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('theme.footer')

{{-- JavaScript --}}
<script>
document.addEventListener("DOMContentLoaded", function () {
  // Initialize Select2 for employee dropdown
  $('.select2-employee').select2({
    placeholder: "Hitamo Umukozi",
    allowClear: true,
    width: '100%'
  });

  // Row management for the form
  let rowIndex = 0;
  const addRowBtn = document.getElementById("addRowBtn");
  const rowsContainer = document.getElementById("rowsContainer");

  const categories = @json($categories);
  const stockTypes = @json($stockTypes);

  // IDs of transport categories that require brick status
  const amatafariCategoryIds = categories
    .filter(cat => cat.name.toLowerCase().includes('amatafari'))
    .map(cat => parseInt(cat.id));

  // Function to add a new row
  const addRow = () => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>
        <select name="records[${rowIndex}][transport_category_id]" 
                class="form-select category-select" data-index="${rowIndex}" required>
          <option value="">-- Hitamo --</option>
          ${categories.map(cat => `<option value="${cat.id}">${cat.name} - ${cat.unit_price} Rwf</option>`).join('')}
        </select>
      </td>
      <td>
        <div class="brick-status-wrapper" id="brickStatusWrapper${rowIndex}" style="display: none;">
          <select name="records[${rowIndex}][brick_status]" class="form-select">
            <option value="">-- Hitamo ubwoko --</option>
            ${stockTypes.map(type => `<option value="${type.id}">${type.name}</option>`).join('')}
          </select>
        </div>
      </td>
      <td>
        <input type="number" name="records[${rowIndex}][quantity]" 
               class="form-control" min="1" required>
      </td>
      <td>
        <input type="text" name="records[${rowIndex}][destination]" 
               class="form-control" placeholder="Aho byakorewe">
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-danger remove-btn" title="Remove">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    `;
    rowsContainer.appendChild(row);
    
    // Initialize Select2 for the new category select
    $(row.querySelector('.category-select')).select2({
      placeholder: "Hitamo igikorwa",
      width: '100%'
    });
    
    rowIndex++;
  };

  // Add first row initially
  addRow();

  // Add new rows
  addRowBtn.addEventListener("click", addRow);

  // Remove row
  rowsContainer.addEventListener("click", (e) => {
    if (e.target.closest(".remove-btn")) {
      const row = e.target.closest("tr");
      if (rowsContainer.children.length > 1) row.remove();
    }
  });

  // Show/hide stock status select based on category
  rowsContainer.addEventListener("change", (e) => {
    if (e.target.classList.contains("category-select")) {
      const idx = e.target.dataset.index;
      const selectedCategoryId = parseInt(e.target.value);
      const brickStatusDiv = document.getElementById(`brickStatusWrapper${idx}`);

      if (amatafariCategoryIds.includes(selectedCategoryId)) {
        brickStatusDiv.style.display = "block";
        brickStatusDiv.querySelector("select").setAttribute("required", "true");
      } else {
        brickStatusDiv.style.display = "none";
        brickStatusDiv.querySelector("select").removeAttribute("required");
      }
    }
  });

  // Date range modal initialization
  const dateRangeModal = document.getElementById('dateRangeModal');
  if (dateRangeModal) {
    dateRangeModal.addEventListener('show.bs.modal', function () {
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('startDate').value = today;
      document.getElementById('endDate').value = today;
    });
  }

  // Auto-submit form when date range is selected
  const dateRangeForm = document.querySelector('#dateRangeModal form');
  if (dateRangeForm) {
    dateRangeForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const startDate = document.getElementById('startDate').value;
      const endDate = document.getElementById('endDate').value;
      
      // Update the URL with the date range
      const url = new URL(window.location.href);
      url.searchParams.set('date_range', `${startDate} - ${endDate}`);
      url.searchParams.delete('filter');
      
      window.location.href = url.toString();
    });
  }

  // Make table rows clickable for better UX
  document.querySelectorAll('table tbody tr').forEach(row => {
    row.style.cursor = 'pointer';
    row.addEventListener('click', (e) => {
      // Don't trigger if clicking on a button or link
      if (!e.target.closest('a, button')) {
        const viewBtn = row.querySelector('a.btn');
        if (viewBtn) {
          viewBtn.click();
        }
      }
    });
  });
});



document.addEventListener("DOMContentLoaded", function () {
  let rowIndex = 0;
  const addRowBtn = document.getElementById("addRowBtn");
  const rowsContainer = document.getElementById("rowsContainer");

  const categories = @json($categories);
  const stockTypes = @json($stockTypes);

  // IDs of transport categories that require brick status
  const amatafariCategoryIds = categories
    .filter(cat => cat.name.toLowerCase().includes('amatafari'))
    .map(cat => parseInt(cat.id));

  // Function to add a new row
  const addRow = () => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>
        <select name="records[${rowIndex}][transport_category_id]" 
                class="form-select category-select" data-index="${rowIndex}" required>
          <option value="">-- Hitamo --</option>
          ${categories.map(cat => `<option value="${cat.id}">${cat.name} - ${cat.unit_price} Rwf</option>`).join('')}
        </select>
      </td>
      <td>
        <div class="brick-status-wrapper" id="brickStatusWrapper${rowIndex}" style="display: none;">
          <select name="records[${rowIndex}][brick_status]" class="form-select">
            <option value="">-- Hitamo ubwoko --</option>
            ${stockTypes.map(type => `<option value="${type.id}">${type.name}</option>`).join('')}
          </select>
        </div>
      </td>
      <td>
        <input type="number" name="records[${rowIndex}][quantity]" 
               class="form-control" min="1" required>
      </td>
      <td>
        <input type="text" name="records[${rowIndex}][destination]" 
               class="form-control" placeholder="Aho byakorewe">
      </td>
      <td class="text-center">
        <button type="button" class="btn btn-sm btn-danger remove-btn" title="Remove">
          <i class="bi bi-trash"></i>
        </button>
      </td>
    `;
    rowsContainer.appendChild(row);
    rowIndex++;
  };

  // Add first row initially
  addRow();

  // Add new rows
  addRowBtn.addEventListener("click", addRow);

  // Remove row
  rowsContainer.addEventListener("click", (e) => {
    if (e.target.closest(".remove-btn")) {
      const row = e.target.closest("tr");
      if (rowsContainer.children.length > 1) row.remove();
    }
  });

  // Show/hide stock status select based on category
  rowsContainer.addEventListener("change", (e) => {
    if (e.target.classList.contains("category-select")) {
      const idx = e.target.dataset.index;
      const selectedCategoryId = parseInt(e.target.value);
      const brickStatusDiv = document.getElementById(`brickStatusWrapper${idx}`);

      if (amatafariCategoryIds.includes(selectedCategoryId)) {
        brickStatusDiv.style.display = "block";
        brickStatusDiv.querySelector("select").setAttribute("required", "true");
      } else {
        brickStatusDiv.style.display = "none";
        brickStatusDiv.querySelector("select").removeAttribute("required");
      }
    }
  });
});
</script>