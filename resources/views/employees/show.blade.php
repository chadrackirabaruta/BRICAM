@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <!-- Page Title -->
  <div class="pagetitle d-flex justify-content-between align-items-center mb-3">
    <h1><i class="bi bi-person-badge me-2"></i> Employee Details</h1>
    <div>
      <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary me-2">
        <i class="bi bi-pencil-square"></i> Edit
      </a>
      <a href="{{ route('employees.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left-circle"></i> Back to List
      </a>
    </div>
  </div>

  <!-- Profile Section -->
  <section class="section profile">
    <div class="row">

      <!-- Left Column: Profile Card -->
      <div class="col-xl-4">
        <div class="card">
          <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">

         <img src="{{ $employee->avatar ? asset('storage/' . $employee->avatar) : asset('images/default-avatar.png') }}"
     onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
     alt="Profile" class="rounded-circle shadow" width="120">

            <h2 class="mt-3">{{ $employee->name }}</h2>
            <h6>ID: {{ $employee->id_number ?? 'N/A' }}</h6>
            <span class="badge bg-{{ $employee->active ? 'success' : 'secondary' }}">
              {{ $employee->active ? 'Active' : 'Inactive' }}
            </span>

            <div class="mt-3">
              @if($employee->phone)
                <a href="tel:{{ $employee->phone }}" class="btn btn-sm btn-outline-primary me-1">
                  <i class="bi bi-telephone"></i> Call
                </a>
              @endif
              @if($employee->email)
                <a href="mailto:{{ $employee->email }}" class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-envelope"></i> Email
                </a>
              @endif
            </div>
          </div>
        </div>

        <!-- Additional Info Card -->
        <div class="card mt-3">
          <div class="card-body">
            <h5 class="card-title text-primary">
              <i class="bi bi-calendar-check"></i> Additional Info
            </h5>
            <div class="row mb-2">
              <div class="col-6 fw-bold">Date of Birth</div>
              <div class="col-6">{{ $employee->dob ? $employee->dob->format('M d, Y') : 'N/A' }}</div>
            </div>
            <div class="row mb-2">
              <div class="col-6 fw-bold">Age</div>
              <div class="col-6">{{ $age ?? 'N/A' }}</div>
            </div>
            <div class="row mb-2">
              <div class="col-6 fw-bold">Gender</div>
              <div class="col-6">{{ $employee->gender ?? 'N/A' }}</div>
            </div>
            <div class="row">
              <div class="col-6 fw-bold">Country</div>
              <div class="col-6">{{ $employee->country ?? 'N/A' }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Details -->
      <div class="col-xl-8">
        <div class="card">
          <div class="card-body pt-3">

            <h5 class="card-title mb-4 text-primary">
              <i class="bi bi-person-lines-fill"></i> Contact Information
            </h5>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Email</div>
              <div class="col-sm-8">
                @if($employee->email)
                  <a href="mailto:{{ $employee->email }}">{{ $employee->email }}</a>
                @else
                  N/A
                @endif
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Phone</div>
              <div class="col-sm-8">
                @if($employee->phone)
                  <a href="tel:{{ $employee->phone }}">{{ $employee->phone }}</a>
                @else
                  N/A
                @endif
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Joined On</div>
              <div class="col-sm-8">{{ $employee->created_at?->format('M d, Y') ?? 'N/A' }}</div>
            </div>

            <h5 class="card-title mt-4 text-primary">
              <i class="bi bi-building"></i> Job Details
            </h5>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Employee Type</div>
              <div class="col-sm-8">{{ $employee->employeeType->name ?? 'N/A' }}</div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Salary Type</div>
              <div class="col-sm-8">{{ $employee->salaryType->name ?? 'N/A' }}</div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Last Updated</div>
              <div class="col-sm-8">{{ $employee->updated_at->format('M d, Y h:i A') }}</div>
            </div>

            <h5 class="card-title mt-4 text-primary">
              <i class="bi bi-geo-alt-fill"></i> Address Details
            </h5>

            @php
              $addressFields = [
                  'Province' => $employee->province,
                  'District' => $employee->district,
                  'Sector'   => $employee->sector,
                  'Cell'     => $employee->cell,
                  'Village'  => $employee->village,
                  'Full Address' => $employee->address
              ];
            @endphp

            @foreach($addressFields as $label => $value)
              @if($value)
              <div class="row mb-3">
                <div class="col-sm-4 fw-bold">{{ $label }}</div>
                <div class="col-sm-8">{{ $value }}</div>
              </div>
              @endif
            @endforeach

          </div>
        </div>

        <!-- Employee Activity Section -->
        <div class="card mt-3">
          <div class="card-body">
            <h5 class="card-title text-primary">
              <i class="bi bi-activity"></i> Employee Activity
            </h5>
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i> Employee activity log will appear here.
            </div>
            <!-- Placeholder: Add logs, attendance, or project history here -->
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

@include('theme.footer')