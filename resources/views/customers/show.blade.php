@include('theme.head')
@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <div class="pagetitle d-flex justify-content-between align-items-center mb-3">
    <h1><i class="bi bi-person-badge me-2"></i> Customer Details</h1>
    <div>
      <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary me-2">
        <i class="bi bi-pencil-square"></i> Edit
      </a>
      <a href="{{ route('customers.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left-circle"></i> Back to List
      </a>
    </div>
  </div>

  <!-- Profile card -->
  <section class="section profile">
    <div class="row">
      <div class="col-xl-4">
        <div class="card">
          <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
            <img src="{{ $customer->avatar ? asset('storage/'.$customer->avatar) : asset('images/default-avatar.png') }}"
                 onerror="this.onerror=null;this.src='{{ asset('images/default-avatar.png') }}'"
                 alt="Profile" class="rounded-circle shadow" width="120">

            <h2 class="mt-3">{{ $customer->name }}</h2>
            <h6>ID: {{ $customer->id_number ?? 'N/A' }}</h6>
            <span class="badge bg-{{ $customer->active ? 'success' : 'secondary' }}">
              {{ $customerTypes[$customer->customer_type] ?? $customer->customer_type }}
            </span>
            
            <div class="mt-3">
              @if($customer->phone)
                <a href="tel:{{ $customer->phone }}" class="btn btn-sm btn-outline-primary me-1">
                  <i class="bi bi-telephone"></i> Call
                </a>
              @endif
              @if($customer->email)
                <a href="mailto:{{ $customer->email }}" class="btn btn-sm btn-outline-primary">
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
              <div class="col-6">{{ $customer->dob ? $customer->dob->format('M d, Y') : 'N/A' }}</div>
            </div>
            <div class="row mb-2">
              <div class="col-6 fw-bold">Age</div>
              <div class="col-6">{{ $age ?? 'N/A' }}</div>
            </div>
            <div class="row mb-2">
              <div class="col-6 fw-bold">Gender</div>
              <div class="col-6">{{ $customer->gender ?? 'N/A' }}</div>
            </div>
            <div class="row">
              <div class="col-6 fw-bold">Country</div>
              <div class="col-6">{{ $customer->country }}</div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-8">
        <div class="card">
          <div class="card-body pt-3">
            <h5 class="card-title mb-4 text-primary">
              <i class="bi bi-person-lines-fill"></i> Contact Information
            </h5>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Email</div>
              <div class="col-sm-8">
                @if($customer->email)
                  <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                @else
                  N/A
                @endif
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Phone</div>
              <div class="col-sm-8">
                @if($customer->phone)
                  <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                @else
                  N/A
                @endif
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Customer Since</div>
              <div class="col-sm-8">{{ $customer->created_at->format('M d, Y') }}</div>
            </div>

            <h5 class="card-title mt-4 text-primary">
              <i class="bi bi-geo-alt-fill"></i> Address Details
            </h5>

            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Province</div>
              <div class="col-sm-8">{{ $customer->province }}</div>
            </div>
            
            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">District</div>
              <div class="col-sm-8">{{ $customer->district }}</div>
            </div>
            
            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Sector</div>
              <div class="col-sm-8">{{ $customer->sector }}</div>
            </div>
            
            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Cell</div>
              <div class="col-sm-8">{{ $customer->cell }}</div>
            </div>
            
            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Village</div>
              <div class="col-sm-8">{{ $customer->village }}</div>
            </div>
            
            @if($customer->address)
            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Full Address</div>
              <div class="col-sm-8">{{ $customer->address }}</div>
            </div>
            @endif

            <h5 class="card-title mt-4 text-primary">
              <i class="bi bi-card-checklist"></i> Additional Information
            </h5>
            
            <div class="row mb-3">
              <div class="col-sm-4 fw-bold">Last Updated</div>
              <div class="col-sm-8">{{ $customer->updated_at->format('M d, Y h:i A') }}</div>
            </div>
          </div>
        </div>
        
        <!-- Customer Activity Section -->
        <div class="card mt-3">
          <div class="card-body">
            <h5 class="card-title text-primary">
              <i class="bi bi-activity"></i> Customer Activity
            </h5>
            <div class="alert alert-info">
              <i class="bi bi-info-circle"></i> Activity tracking will be displayed here
            </div>
            <!-- Placeholder for future activity logs or purchase history -->
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

@include('theme.footer')