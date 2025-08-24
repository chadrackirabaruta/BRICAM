<!DOCTYPE html>
<html lang="en">

@include('theme.head')

<body>

@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center">
    <h1><i class="bi bi-pencil-square"></i> Edit Employee</h1>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary">
      <i class="bi bi-arrow-left-circle"></i> Back
    </a>
  </div>

  @include('theme.success')

  @if ($errors->any())
    <div class="alert alert-danger">
      <strong>There were some issues:</strong>
      <ul class="mb-0 mt-2">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif
  <section class="section">
    <div class="card">
      <div class="card-body pt-4">
        <h5 class="card-title text-primary"><i class="bi bi-pencil-square"></i> Edit Employee Info</h5>

        <form action="{{ route('employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <!-- ========== BASIC INFORMATION SECTION ========== -->
          <div class="pt-3">
            <h5 class="text-primary fw-bold mb-3">
              <i class="bi bi-person-lines-fill"></i> Basic Information
            </h5>
            
            <div class="row g-3">
              <!-- Status Field -->
              <div class="col-md-6">
                <label for="status" class="form-label">Employment Status <span class="text-danger">*</span></label>
                <select name="status" id="status" class="form-select" required>
                  <option value="active" {{ old('status', $employee->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                  <option value="inactive" {{ old('status', $employee->status ?? 'active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
              </div>
              
              <!-- Full Name -->
              <div class="col-md-6">
                <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $employee->name) }}" required>
              </div>

              <!-- ID Number -->
              <div class="col-md-6">
                <label for="id_number" class="form-label">ID Number <span class="text-danger">*</span></label>
                <input type="text" name="id_number" id="id_number" class="form-control" maxlength="16" value="{{ old('id_number', $employee->id_number) }}" required>
                <div class="form-text">Must be 16 digits</div>
              </div>

              <!-- Phone -->
              <div class="col-md-6">
                <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                <input type="text" name="phone" id="phone" class="form-control" maxlength="10" value="{{ old('phone', $employee->phone) }}" required>
                <div class="form-text">Must be 10 digits</div>
              </div>

              <!-- Email Address -->
              <div class="col-md-6">
                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email', $employee->email) }}" class="form-control" required>
              </div>

              <!-- Date of Birth -->
              <div class="col-md-6">
                <label for="dob" class="form-label"><i class="bi bi-calendar2-day"></i> Date of Birth</label>
                <input type="date" name="dob" id="dob" class="form-control" value="{{ old('dob', $employee->dob ? $employee->dob->format('Y-m-d') : '') }}">
              </div>

              <!-- Gender -->
              <div class="col-md-6">
                <label class="form-label">Gender <span class="text-danger">*</span></label><br>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="gender" id="male" value="Male" {{ old('gender', $employee->gender) === 'Male' ? 'checked' : '' }}>
                  <label class="form-check-label" for="male">Male</label>
                </div>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="gender" id="female" value="Female" {{ old('gender', $employee->gender) === 'Female' ? 'checked' : '' }}>
                  <label class="form-check-label" for="female">Female</label>
                </div>
              </div>

              <!-- Avatar -->
              <div class="col-md-6">
                <label for="avatar" class="form-label"><i class="bi bi-image"></i> Profile Photo</label>
                <div class="d-flex align-items-center">
                  @if($employee->avatar)
                    <img src="{{ asset('storage/' . $employee->avatar) }}" class="rounded-circle me-3" width="50" height="50" alt="Avatar">
                  @else
                    <img src="{{ asset('default-avatar.png') }}" class="rounded-circle me-3" width="50" height="50" alt="Default Avatar">
                  @endif
                  <input type="file" name="avatar" id="avatar" class="form-control" accept="image/*">
                </div>
              </div>
            </div>
          </div>

          <!-- ========== EMPLOYMENT DETAILS SECTION ========== -->
          <div class="pt-4">
            <h5 class="text-primary fw-bold mb-3">
              <i class="bi bi-briefcase-fill"></i> Employment Details
            </h5>
            
            <div class="row g-3">
              <!-- Employee Type -->
              <div class="col-md-6">
                <label for="employee_type_id" class="form-label">Employee Type <span class="text-danger">*</span></label>
                <select name="employee_type_id" class="form-select" required>
                  <option value="">-- Select Type --</option>
                  @foreach($employeeTypes as $type)
                    <option value="{{ $type->id }}" {{ old('employee_type_id', $employee->employee_type_id) == $type->id ? 'selected' : '' }}>
                      {{ $type->name }}
                    </option>
                  @endforeach
                </select>
              </div>

              <!-- Salary Type -->
              <div class="col-md-6">
                <label for="salary_type_id" class="form-label">Salary Type <span class="text-danger">*</span></label>
                <select name="salary_type_id" class="form-select" required>
                  <option value="">-- Select Salary Type --</option>
                  @foreach($salaryTypes as $type)
                    <option value="{{ $type->id }}" {{ old('salary_type_id', $employee->salary_type_id) == $type->id ? 'selected' : '' }}>
                      {{ $type->name }}
                    </option>
                  @endforeach
                </select>
              </div>

            

          <!-- ========== LOCATION SECTION ========== -->
          <div class="pt-4">
            <h5 class="text-primary fw-bold mb-3">
              <i class="bi bi-geo-alt-fill"></i> Location Information
            </h5>

            <div class="row g-3">
              <!-- Country -->
              <div class="col-md-6">
                <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                <input type="text" name="country" id="country" class="form-control" value="{{ old('country', $employee->country ?? 'Rwanda') }}" required>
              </div>

              <!-- Province -->
              <div class="col-md-6">
                <label for="province" class="form-label">Province <span class="text-danger">*</span></label>
                <select id="province" name="province" class="form-select" required>
                  <option value="{{ old('province', $employee->province) }}">{{ old('province', $employee->province) }}</option>
                </select>
              </div>

              <!-- District -->
              <div class="col-md-6">
                <label for="district" class="form-label">District <span class="text-danger">*</span></label>
                <select id="district" name="district" class="form-select" required>
                  <option value="{{ old('district', $employee->district) }}">{{ old('district', $employee->district) }}</option>
                </select>
              </div>

              <!-- Sector -->
              <div class="col-md-6">
                <label for="sector" class="form-label">Sector <span class="text-danger">*</span></label>
                <select id="sector" name="sector" class="form-select" required>
                  <option value="{{ old('sector', $employee->sector) }}">{{ old('sector', $employee->sector) }}</option>
                </select>
              </div>

              <!-- Cell -->
              <div class="col-md-6">
                <label for="cell" class="form-label">Cell <span class="text-danger">*</span></label>
                <select id="cell" name="cell" class="form-select" required>
                  <option value="{{ old('cell', $employee->cell) }}">{{ old('cell', $employee->cell) }}</option>
                </select>
              </div>

              <!-- Village -->
              <div class="col-md-6">
                <label for="village" class="form-label">Village <span class="text-danger">*</span></label>
                <select id="village" name="village" class="form-select" required>
                  <option value="{{ old('village', $employee->village) }}">{{ old('village', $employee->village) }}</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Form Buttons -->
          <div class="text-end mt-4">
            <a href="{{ route('employees.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Update Employee</button>
          </div>
        </form>
      </div>
    </div>
  </section>
</main>

@include('theme.footer')

</body>
</html>