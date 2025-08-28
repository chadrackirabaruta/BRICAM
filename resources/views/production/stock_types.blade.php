@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <!-- Page Title and Add Button -->
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1><i class="bi bi-box-seam"></i> Stock Types</h1>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStockTypeModal">
      <i class="bi bi-plus-circle"></i> Add New
    </button>
  </div>

  <!-- Flash Message for Success -->
  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
      {{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <section class="section mt-4">
    <div class="card shadow-sm border-0">
      <div class="card-body p-4">
        <h5 class="card-title text-primary"><i class="bi bi-table"></i> All Stock Types</h5>

        <div class="table-responsive">
          <table id="stockTypeTable" class="table table-striped table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Flow Stage</th>
                <th>Parent</th>
                <th>Decrease From</th>
                <th>Decrease Amount</th>
                <th>Increase To</th>
                <th>Increase Amount</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($stockTypes as $key => $type)
              <tr>
                <td>{{ $key + 1 }}</td>
                <td><strong>{{ $type->name }}</strong></td>
                <td>{{ $type->flow_stage }}</td>
                <td>{{ $type->parent?->name ?? '-' }}</td>
                <td>{{ $type->decrease_from ?? '-' }}</td>
                <td>{{ $type->decrease_amount ?? '-' }}</td>
                <td>{{ $type->increase_to ?? '-' }}</td>
                <td>{{ $type->increase_amount ?? '-' }}</td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#editStockTypeModal{{ $type->id }}">
                    <i class="bi bi-pencil"></i>
                  </button>
                </td>
              </tr>

              {{-- Move the modal OUTSIDE the table via a stack to prevent shaking --}}
              @push('modals')
              <!-- Edit Modal -->
              <div class="modal fade" id="editStockTypeModal{{ $type->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                  <div class="modal-content">
                    <form method="POST" action="{{ route('stock_types.update', $type->id) }}">
                      @csrf
                      @method('PUT')
                      <div class="modal-header">
                        <h5 class="modal-title">Edit Stock Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required value="{{ old('name', $type->name) }}">
                          </div>

                          <div class="col-md-6 mb-3">
                            <label class="form-label">Flow Stage <span class="text-danger">*</span></label>
                            <input type="number" name="flow_stage" class="form-control" required value="{{ old('flow_stage', $type->flow_stage) }}">
                          </div>

                          <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Type</label>
                            <select name="parent_id" class="form-select">
                              <option value="">-- None --</option>
                              @foreach($stockTypes as $parent)
                                @if($parent->id != $type->id)
                                  <option value="{{ $parent->id }}" {{ old('parent_id', $type->parent_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->name }}
                                  </option>
                                @endif
                              @endforeach
                            </select>
                          </div>

                          <div class="col-md-6 mb-3">
                            <label class="form-label">Decrease From</label>
                            <input type="number" name="decrease_from" class="form-control" value="{{ old('decrease_from', $type->decrease_from) }}">
                          </div>

                          <div class="col-md-6 mb-3">
                            <label class="form-label">Decrease Amount</label>
                            <input type="number" name="decrease_amount" step="0.01" class="form-control" value="{{ old('decrease_amount', $type->decrease_amount) }}">
                          </div>

                          <div class="col-md-6 mb-3">
                            <label class="form-label">Increase To</label>
                            <input type="number" name="increase_to" class="form-control" value="{{ old('increase_to', $type->increase_to) }}">
                          </div>

                          <div class="col-md-6 mb-3">
                            <label class="form-label">Increase Amount</label>
                            <input type="number" name="increase_amount" step="0.01" class="form-control" value="{{ old('increase_amount', $type->increase_amount) }}">
                          </div>
                        </div>
                      </div>
                      <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                          <i class="bi bi-save"></i> Update
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                          Cancel
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
              @endpush
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>
</main>

{{-- Render all pushed modals OUTSIDE the table to prevent shake --}}
@stack('modals')
<!-- Add New Modal -->
<div class="modal fade" id="addStockTypeModal" tabindex="-1" aria-labelledby="addStockTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form method="POST" action="{{ route('stock_types.store') }}" id="createForm">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addStockTypeModalLabel"><i class="bi bi-plus-circle"></i> Add Stock Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetCreateForm()"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Flow Stage <span class="text-danger">*</span></label>
              <input type="number" name="flow_stage" class="form-control" required value="{{ old('flow_stage') }}">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Parent Type</label>
              <select name="parent_id" class="form-select">
                <option value="">-- None --</option>
                @foreach($stockTypes as $parent)
                  <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                    {{ $parent->name }}
                  </option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Decrease From</label>
              <input type="number" name="decrease_from" class="form-control" value="{{ old('decrease_from') }}">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Decrease Amount</label>
              <input type="number" name="decrease_amount" step="0.01" class="form-control" value="{{ old('decrease_amount') }}">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Increase To</label>
              <input type="number" name="increase_to" class="form-control" value="{{ old('increase_to') }}">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Increase Amount</label>
              <input type="number" name="increase_amount" step="0.01" class="form-control" value="{{ old('increase_amount') }}">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Save</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="resetCreateForm()">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Shared Edit Modal (single instance, outside the table) -->
<div class="modal fade" id="editStockTypeModal" tabindex="-1" aria-labelledby="editStockTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form method="POST" id="editForm" action="">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h5 class="modal-title" id="editStockTypeModalLabel">Edit Stock Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Name <span class="text-danger">*</span></label>
              <input type="text" name="name" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Flow Stage <span class="text-danger">*</span></label>
              <input type="number" name="flow_stage" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Parent Type</label>
              <select name="parent_id" class="form-select" id="edit_parent_id">
                <option value="">-- None --</option>
                @foreach($stockTypes as $parent)
                  <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Decrease From</label>
              <input type="number" name="decrease_from" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Decrease Amount</label>
              <input type="number" name="decrease_amount" step="0.01" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Increase To</label>
              <input type="number" name="increase_to" class="form-control">
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Increase Amount</label>
              <input type="number" name="increase_amount" step="0.01" class="form-control">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> Update
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Init DataTable for stock types
    if (window.simpleDatatables) {
      new simpleDatatables.DataTable("#stockTypeTable", {
        searchable: true,
        perPageSelect: [10, 25, 50, 100],
        perPage: 10,
        fixedHeight: false
      });
    }

    const modalEl = document.getElementById('editStockTypeModal');
    const formEl  = modalEl.querySelector('#editForm');

    // Helper to fill the modal from a button element
    function fillModalFromButton(btn) {
      if (!btn) return;

      const get = (attr) => btn.getAttribute(attr);

      // Set form action
      formEl.setAttribute('action', get('data-action') || '');

      // Set inputs
      const set = (name, val) => {
        const el = modalEl.querySelector(`[name="${name}"]`);
        if (!el) return;
        if (val === null || val === undefined || val === 'null') val = '';
        el.value = val;
      };

      set('name',            get('data-name'));
      set('flow_stage',      get('data-flow-stage'));
      set('decrease_from',   get('data-decrease-from'));
      set('decrease_amount', get('data-decrease-amount'));
      set('increase_to',     get('data-increase-to'));
      set('increase_amount', get('data-increase-amount'));

      // Parent select + prevent self-parenting
      const currentId = get('data-id') || '';
      const parentId  = get('data-parent-id') || '';
      const parentSelect = modalEl.querySelector('#edit_parent_id');
      if (parentSelect) {
        [...parentSelect.options].forEach(o => o.disabled = (o.value === currentId));
        parentSelect.value = (parentId && parentId !== currentId) ? parentId : '';
      }
    }

    // Programmatic open on click (ensures values are set before showing)
    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.js-edit-stock-type');
      if (!btn) return;

      e.preventDefault();
      window._lastEditBtn = btn; // keep reference as fallback
      fillModalFromButton(btn);

      if (window.bootstrap && window.bootstrap.Modal) {
        const instance = window.bootstrap.Modal.getOrCreateInstance(modalEl);
        instance.show();
      } else {
        // Fallback: trigger via attribute if bootstrap global not found
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        modalEl.removeAttribute('aria-hidden');
      }
    });

    // Fallback population on show (in case something else triggered the modal)
    modalEl.addEventListener('show.bs.modal', function (event) {
      const fromTarget = event.relatedTarget || window._lastEditBtn;
      if (fromTarget) fillModalFromButton(fromTarget);
    });

    // Cleanup after hide
    modalEl.addEventListener('hidden.bs.modal', function () {
      formEl.reset();
      const parentSelect = modalEl.querySelector('#edit_parent_id');
      if (parentSelect) [...parentSelect.options].forEach(o => o.disabled = false);
      formEl.removeAttribute('action');
    });
  });

  // Reset form on Add modal close
  function resetCreateForm() {
    const f = document.getElementById("createForm");
    if (f) f.reset();
  }
</script>
@endpush

@include('theme.footer')