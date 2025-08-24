@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
    <!-- Title + Buttons -->
    <div class="pagetitle d-flex justify-content-between align-items-center">
        <h1><i class="bi bi-tags"></i> Category Management</h1>
    </div>
    
    @include('theme.success')
    
    <div class="row">
        <!-- Employee Types Card -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title text-primary">
                            <i class="bi bi-people"></i> Employee Types
                        </h5>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addEmployeeTypeModal">
                            <i class="bi bi-plus-circle"></i> Add
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employeeTypes as $key => $type)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $type->name }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary edit-employee-type-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editEmployeeTypeModal{{ $type->id }}"
                                                data-id="{{ $type->id }}"
                                                data-name="{{ $type->name }}"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('employee-types.destroy', $type->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger delete-btn" 
                                                    data-item-name="{{ $type->name }}"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-secondary">No employee types found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Salary Types Card -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title text-primary">
                            <i class="bi bi-cash-coin"></i> Salary Types
                        </h5>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addSalaryTypeModal">
                            <i class="bi bi-plus-circle"></i> Add
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salaryTypes as $key => $type)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $type->name }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary edit-salary-type-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editSalaryTypeModal{{ $type->id }}"
                                                data-id="{{ $type->id }}"
                                                data-name="{{ $type->name }}"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('salary-types.destroy', $type->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger delete-btn" 
                                                    data-item-name="{{ $type->name }}"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-secondary">No salary types found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Transport Categories Card -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title text-primary">
                            <i class="bi bi-truck"></i> Transport Categories
                        </h5>
                        <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addTransportCategoryModal">
                            <i class="bi bi-plus-circle"></i> Add
                        </button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Unit Price</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transportCategories as $key => $category)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ number_format($category->unit_price, 2) }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary edit-transport-category-btn" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editTransportCategoryModal{{ $category->id }}"
                                                data-id="{{ $category->id }}"
                                                data-name="{{ $category->name }}"
                                                data-unit-price="{{ $category->unit_price }}"
                                                title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('transport-categories.destroy', $category->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger delete-btn" 
                                                    data-item-name="{{ $category->name }}"
                                                    title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-secondary">No transport categories found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Add Employee Type Modal -->
<div class="modal fade" id="addEmployeeTypeModal" tabindex="-1" aria-labelledby="addEmployeeTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('employee-types.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addEmployeeTypeModalLabel">
                    <i class="bi bi-plus-circle"></i> Add Employee Type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="add_employee_type_name" class="form-label">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="add_employee_type_name" name="name" 
                           value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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

<!-- Add Salary Type Modal -->
<div class="modal fade" id="addSalaryTypeModal" tabindex="-1" aria-labelledby="addSalaryTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('salary-types.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addSalaryTypeModalLabel">
                    <i class="bi bi-plus-circle"></i> Add Salary Type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="add_salary_type_name" class="form-label">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="add_salary_type_name" name="name" 
                           value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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

<!-- Add Transport Category Modal -->
<div class="modal fade" id="addTransportCategoryModal" tabindex="-1" aria-labelledby="addTransportCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('transport-categories.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addTransportCategoryModalLabel">
                    <i class="bi bi-plus-circle"></i> Add Transport Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="add_transport_category_name" class="form-label">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="add_transport_category_name" name="name" 
                           value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="add_transport_category_unit_price" class="form-label">Unit Price</label>
                    <input type="number" step="0.01" class="form-control @error('unit_price') is-invalid @enderror" 
                           id="add_transport_category_unit_price" name="unit_price" 
                           value="{{ old('unit_price') }}" required>
                    @error('unit_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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

<!-- Edit Employee Type Modals -->
@foreach($employeeTypes as $type)
<div class="modal fade" id="editEmployeeTypeModal{{ $type->id }}" tabindex="-1" 
     aria-labelledby="editEmployeeTypeModalLabel{{ $type->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('employee-types.update', $type->id) }}" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editEmployeeTypeModalLabel{{ $type->id }}">
                    <i class="bi bi-pencil"></i> Edit Employee Type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edit_employee_type_name_{{ $type->id }}" class="form-label">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="edit_employee_type_name_{{ $type->id }}" name="name" 
                           value="{{ old('name', $type->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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

<!-- Edit Salary Type Modals -->
@foreach($salaryTypes as $type)
<div class="modal fade" id="editSalaryTypeModal{{ $type->id }}" tabindex="-1" 
     aria-labelledby="editSalaryTypeModalLabel{{ $type->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('salary-types.update', $type->id) }}" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editSalaryTypeModalLabel{{ $type->id }}">
                    <i class="bi bi-pencil"></i> Edit Salary Type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edit_salary_type_name_{{ $type->id }}" class="form-label">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="edit_salary_type_name_{{ $type->id }}" name="name" 
                           value="{{ old('name', $type->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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

<!-- Edit Transport Category Modals -->
@foreach($transportCategories as $category)
<div class="modal fade" id="editTransportCategoryModal{{ $category->id }}" tabindex="-1" 
     aria-labelledby="editTransportCategoryModalLabel{{ $category->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('transport-categories.update', $category->id) }}" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="editTransportCategoryModalLabel{{ $category->id }}">
                    <i class="bi bi-pencil"></i> Edit Transport Category
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edit_transport_category_name_{{ $category->id }}" class="form-label">Name</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                           id="edit_transport_category_name_{{ $category->id }}" name="name" 
                           value="{{ old('name', $category->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="edit_transport_category_unit_price_{{ $category->id }}" class="form-label">Unit Price</label>
                    <input type="number" step="0.01" class="form-control @error('unit_price') is-invalid @enderror" 
                           id="edit_transport_category_unit_price_{{ $category->id }}" name="unit_price" 
                           value="{{ old('unit_price', $category->unit_price) }}" required>
                    @error('unit_price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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

@include('theme.footer')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced error handling for form submissions
    @if ($errors->any())
        @if(request()->is('employee-types*') && request()->isMethod('post'))
            const employeeModal = new bootstrap.Modal(document.getElementById('addEmployeeTypeModal'));
            employeeModal.show();
        @elseif(request()->is('salary-types*') && request()->isMethod('post'))
            const salaryModal = new bootstrap.Modal(document.getElementById('addSalaryTypeModal'));
            salaryModal.show();
        @elseif(request()->is('transport-categories*') && request()->isMethod('post'))
            const transportModal = new bootstrap.Modal(document.getElementById('addTransportCategoryModal'));
            transportModal.show();
        @elseif(request()->is('transport-categories*') && request()->isMethod('put'))
            const transportEditModal = new bootstrap.Modal(document.getElementById('editTransportCategoryModal{{ old('id') }}'));
            transportEditModal.show();
        @endif
    @endif
    
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Enhanced delete confirmation
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const itemName = this.getAttribute('data-item-name') || 'this item';
            const form = this.closest('form');
            
            if (confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`)) {
                form.submit();
            }
        });
    });
    
    // Modal event listeners for better UX
    document.querySelectorAll('[id^="editEmployeeTypeModal"], [id^="editSalaryTypeModal"], [id^="editTransportCategoryModal"]').forEach(modal => {
        modal.addEventListener('show.bs.modal', function (event) {
            // Clear any previous validation states
            const form = this.querySelector('form');
            const inputs = form.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.remove('is-invalid', 'is-valid');
            });
            
            // Focus on the name input when modal opens
            const nameInput = form.querySelector('input[name="name"]');
            setTimeout(() => {
                if (nameInput) {
                    nameInput.focus();
                    nameInput.select();
                }
            }, 150);
        });
        
        modal.addEventListener('hidden.bs.modal', function (event) {
            // Reset form when modal is closed
            const form = this.querySelector('form');
            if (form) {
                form.reset();
                // Clear validation states
                const inputs = form.querySelectorAll('.form-control');
                inputs.forEach(input => {
                    input.classList.remove('is-invalid', 'is-valid');
                });
            }
        });
    });
    
    // Add modal event listeners
    ['addEmployeeTypeModal', 'addSalaryTypeModal', 'addTransportCategoryModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('show.bs.modal', function (event) {
                // Focus on the name input when modal opens
                const nameInput = this.querySelector('input[name="name"]');
                setTimeout(() => {
                    if (nameInput) {
                        nameInput.focus();
                    }
                }, 150);
            });
            
            modal.addEventListener('hidden.bs.modal', function (event) {
                // Reset form when modal is closed (except when there are errors)
                @if (!$errors->any())
                const form = this.querySelector('form');
                if (form) {
                    form.reset();
                    // Clear validation states
                    const inputs = form.querySelectorAll('.form-control');
                    inputs.forEach(input => {
                        input.classList.remove('is-invalid', 'is-valid');
                    });
                }
                @endif
            });
        }
    });
    
    // Enhanced keyboard navigation
    document.addEventListener('keydown', function(e) {
        // Close modal with Escape key
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                const modal = bootstrap.Modal.getInstance(openModal);
                if (modal) modal.hide();
            }
        }
        
        // Submit form with Ctrl+Enter
        if (e.ctrlKey && e.key === 'Enter') {
            const openModal = document.querySelector('.modal.show');
            if (openModal) {
                const submitBtn = openModal.querySelector('button[type="submit"]');
                if (submitBtn) submitBtn.click();
            }
        }
    });
    
    // Auto-hide success alerts after 5 seconds
    const successAlerts = document.querySelectorAll('.alert-success');
    successAlerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Transport category edit button handler
    document.querySelectorAll('.edit-transport-category-btn').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const unitPrice = this.getAttribute('data-unit-price');
            
            const modal = document.getElementById(`editTransportCategoryModal${id}`);
            if (modal) {
                modal.querySelector('input[name="name"]').value = name;
                modal.querySelector('input[name="unit_price"]').value = unitPrice;
            }
        });
    });
});
</script>
@endpush