<!DOCTYPE html>
<html lang="en">

@include('theme.head')

<body>

@include('theme.header')
@include('theme.sidebar')

<main id="main" class="main">
  <div class="pagetitle">
    <h1><i class="bi bi-person-plus"></i> Create New Customer</h1>
  </div>

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
        <h5 class="card-title text-primary"><i class="bi bi-pencil-square"></i> Customer Registration</h5>

        <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data">
          @csrf

          <div class="row g-3">
            <!-- Full Name -->
            <div class="col-md-6">
              <label for="name" class="form-label"><i class="bi bi-person"></i> Full Name <span class="text-danger">*</span></label>
              <input type="text" name="name" id="name" value="{{ old('name') }}" class="form-control" required>
            </div>
            
            <!-- Customer Type -->
            <div class="col-md-6">
              <label for="customer_type" class="form-label"><i class="bi bi-tags"></i> Customer Type <span class="text-danger">*</span></label>
              <select name="customer_type" id="customer_type" class="form-select" required>
                <option value="Retail" {{ old('customer_type') == 'Retail' ? 'selected' : '' }}>Retail</option>
                <option value="Wholesale" {{ old('customer_type') == 'Wholesale' ? 'selected' : '' }}>Wholesale</option>
                <option value="Contractor" {{ old('customer_type') == 'Contractor' ? 'selected' : '' }}>Contractor</option>
              </select>
            </div>

            <!-- ID Number -->
            <div class="col-md-6">
              <label for="id_number" class="form-label"><i class="bi bi-person-badge"></i> ID Number</label>
              <div class="input-group has-validation position-relative">
                <input type="text" name="id_number" id="id_number" class="form-control" maxlength="16" value="{{ old('id_number') }}">
                <span class="input-group-text bg-light border-start-0" id="id-validation-icon" style="display: none;">
                  <i class="bi" id="id-icon" style="font-size: 1.2rem;"></i>
                </span>
              </div>
              <div class="form-text" id="id-help">Must be exactly 16 digits</div>
            </div>

            <!-- Phone Number -->
            <div class="col-md-6">
              <label for="phone" class="form-label"><i class="bi bi-telephone"></i> Phone Number <span class="text-danger">*</span></label>
              <div class="input-group position-relative">
                <input type="text" name="phone" id="phone" class="form-control" maxlength="10" value="{{ old('phone') }}" required>
                <span class="input-group-text bg-light" id="phone-icon" style="display: none;"><i class="bi"></i></span>
              </div>
              <div class="form-text" id="phone-help">Must be 10 digits (e.g. 078XXXXXXXX)</div>
            </div>

            <!-- Email -->
            <div class="col-md-6">
              <label for="email" class="form-label"><i class="bi bi-envelope"></i> Email Address</label>
              <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control">
            </div>

            <!-- Country -->
            <div class="col-md-6">
              <label for="country" class="form-label"><i class="bi bi-flag"></i> Country <span class="text-danger">*</span></label>
              <input type="text" name="country" id="country" value="{{ old('country', 'Rwanda') }}" class="form-control" required>
            </div>

            <!-- Birth Date -->
            <div class="col-md-6">
              <label for="dob" class="form-label"><i class="bi bi-calendar2-day"></i> Date of Birth</label>
              <input type="date" name="dob" id="dob" class="form-control" value="{{ old('dob') }}">
            </div>

            <!-- Gender -->
            <div class="col-md-6">
              <label class="form-label d-block"><i class="bi bi-gender-ambiguous"></i> Gender</label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" id="male" value="Male" {{ old('gender') == 'Male' ? 'checked' : '' }}>
                <label class="form-check-label" for="male">Male</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" id="female" value="Female" {{ old('gender') == 'Female' ? 'checked' : '' }}>
                <label class="form-check-label" for="female">Female</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" id="other" value="Other" {{ old('gender') == 'Other' ? 'checked' : '' }}>
                <label class="form-check-label" for="other">Other</label>
              </div>
            </div>

            <!-- Avatar -->
            <div class="col-md-6">
              <label for="avatar" class="form-label"><i class="bi bi-image"></i> Profile Photo</label>
              <input type="file" name="avatar" id="avatar" class="form-control" accept="image/*">
            </div>

            <!-- Address -->
            <div class="col-md-6">
              <label for="address" class="form-label"><i class="bi bi-house"></i> Address</label>
              <textarea name="address" id="address" class="form-control">{{ old('address') }}</textarea>
            </div>
          </div>

          <!-- 📍 Location Section -->
          <div class="pt-4">
            <h5 class="text-primary fw-bold mb-3">
              <i class="bi bi-geo-alt-fill"></i> Location
            </h5>

            <div class="row g-3">
              <!-- Province -->
              <div class="col-md-4">
                <label for="province" class="form-label">Intara <span class="text-danger">*</span></label>
                <select id="province" name="province" class="form-select" required>
                  <option value="">-- Hitamo Intara --</option>
                </select>
              </div>

              <!-- District -->
              <div class="col-md-4">
                <label for="district" class="form-label">Akarere <span class="text-danger">*</span></label>
                <select id="district" name="district" class="form-select" required>
                  <option value="">-- Hitamo Akarere --</option>
                </select>
              </div>

              <!-- Sector -->
              <div class="col-md-4">
                <label for="sector" class="form-label">Umurenge <span class="text-danger">*</span></label>
                <select id="sector" name="sector" class="form-select" required>
                  <option value="">-- Hitamo Umurenge --</option>
                </select>
              </div>

              <!-- Cell -->
              <div class="col-md-4">
                <label for="cell" class="form-label">Akagari <span class="text-danger">*</span></label>
                <select id="cell" name="cell" class="form-select" required>
                  <option value="">-- Hitamo Akagari --</option>
                </select>
              </div>

              <!-- Village -->
              <div class="col-md-4">
                <label for="village" class="form-label">Umudugudu <span class="text-danger">*</span></label>
                <select id="village" name="village" class="form-select" required>
                  <option value="">-- Hitamo Umudugudu --</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Submit buttons -->
          <div class="text-end mt-4">
            <a href="{{ route('customers.index') }}" class="btn btn-secondary"><i class="bi bi-arrow-left-circle"></i> Cancel</a>
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Create Customer</button>
          </div>
        </form>
      </div>
    </div>
  </section>
</main>

@include('theme.footer')

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<!-- Validation & Location Script -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  // ✅ ID Number validation
  const idInput = document.getElementById('id_number');
  const idIcon = document.getElementById('id-icon');
  const idHelp = document.getElementById('id-help');
  const idIconWrapper = document.getElementById('id-validation-icon');

  idInput.addEventListener('input', () => {
    const val = idInput.value.replace(/\D/g, '');
    idInput.value = val;
    idIconWrapper.style.display = 'inline-flex';
    if (val.length === 16) {
      idIcon.className = "bi bi-check-circle-fill text-success";
      idHelp.textContent = "✅ ID is valid";
      idHelp.className = "form-text text-success";
    } else if (val.length > 0) {
      idIcon.className = "bi bi-x-circle-fill text-danger";
      idHelp.textContent = "❌ 16 digits required";
      idHelp.className = "form-text text-danger";
    } else {
      idIconWrapper.style.display = 'none';
      idHelp.textContent = "Must be exactly 16 digits";
      idHelp.className = "form-text";
    }
  });

  // ✅ Phone validation
  const phoneInput = document.getElementById('phone');
  const phoneIcon = document.getElementById('phone-icon').querySelector('i');
  const phoneSupport = document.getElementById('phone-help');

  phoneInput.addEventListener('input', () => {
    const num = phoneInput.value.replace(/\D/g, '');
    phoneInput.value = num;
    const wrapper = document.getElementById('phone-icon');
    
    if (num.length > 0) {
      wrapper.style.display = 'inline-flex';
      if (num.length === 10) {
        phoneIcon.className = 'bi bi-check-circle-fill text-success';
        phoneSupport.textContent = "✅ Valid number";
        phoneSupport.className = "form-text text-success";
      } else {
        phoneIcon.className = 'bi bi-x-circle-fill text-danger';
        phoneSupport.textContent = "❌ Must be 10 digits";
        phoneSupport.className = "form-text text-danger";
      }
    } else {
      wrapper.style.display = 'none';
      phoneSupport.textContent = "Must be 10 digits (e.g. 078XXXXXXXX)";
      phoneSupport.className = "form-text";
    }
  });

  // ✅ Load Provinces
  fetch('/api/locations/provinces')
    .then(res => res.json())
    .then(data => {
      const provinceSelect = document.getElementById('province');
      provinceSelect.innerHTML = `<option value="">-- Hitamo Intara --</option>`;
      data.forEach(prov => {
        const selected = prov === "{{ old('province') }}" ? 'selected' : '';
        provinceSelect.innerHTML += `<option value="${prov}" ${selected}>${prov}</option>`;
      });
    });

  // Province → District
  document.getElementById('province').addEventListener('change', function () {
    const val = encodeURIComponent(this.value);
    fetch(`/api/locations/districts/${val}`)
      .then(res => res.json())
      .then(data => {
        const district = document.getElementById('district');
        district.innerHTML = `<option value="">-- Hitamo Akarere --</option>`;
        clearSelects(["sector", "cell", "village"]);
        data.forEach(d => {
          const selected = d === "{{ old('district') }}" ? 'selected' : '';
          district.innerHTML += `<option value="${d}" ${selected}>${d}</option>`;
        });
      });
  });

  // District → Sector
  document.getElementById('district').addEventListener('change', function () {
    const val = encodeURIComponent(this.value);
    fetch(`/api/locations/sectors/${val}`)
      .then(res => res.json())
      .then(data => {
        const sector = document.getElementById('sector');
        sector.innerHTML = `<option value="">-- Hitamo Umurenge --</option>`;
        clearSelects(["cell", "village"]);
        data.forEach(s => {
          const selected = s === "{{ old('sector') }}" ? 'selected' : '';
          sector.innerHTML += `<option value="${s}" ${selected}>${s}</option>`;
        });
      });
  });

  // Sector → Cell
  document.getElementById('sector').addEventListener('change', function () {
    const val = encodeURIComponent(this.value);
    fetch(`/api/locations/cells/${val}`)
      .then(res => res.json())
      .then(data => {
        const cell = document.getElementById('cell');
        cell.innerHTML = `<option value="">-- Hitamo Akagari --</option>`;
        clearSelects(["village"]);
        data.forEach(c => {
          const selected = c === "{{ old('cell') }}" ? 'selected' : '';
          cell.innerHTML += `<option value="${c}" ${selected}>${c}</option>`;
        });
      });
  });

  // Cell → Village
  document.getElementById('cell').addEventListener('change', function () {
    const val = encodeURIComponent(this.value);
    fetch(`/api/locations/villages/${val}`)
      .then(res => res.json())
      .then(data => {
        const village = document.getElementById('village');
        village.innerHTML = `<option value="">-- Hitamo Umudugudu --</option>`;
        data.forEach(v => {
          const selected = v === "{{ old('village') }}" ? 'selected' : '';
          village.innerHTML += `<option value="${v}" ${selected}>${v}</option>`;
        });
      });
  });

  function clearSelects(ids) {
    ids.forEach(id => {
      const el = document.getElementById(id);
      if (el) el.innerHTML = `<option value="">-- Hitamo --</option>`;
    });
  }

  // Initialize location fields if there are old values
  @if(old('province'))
    document.getElementById('province').dispatchEvent(new Event('change'));
    @if(old('district'))
      setTimeout(() => {
        document.getElementById('district').value = "{{ old('district') }}";
        document.getElementById('district').dispatchEvent(new Event('change'));
        @if(old('sector'))
          setTimeout(() => {
            document.getElementById('sector').value = "{{ old('sector') }}";
            document.getElementById('sector').dispatchEvent(new Event('change'));
            @if(old('cell'))
              setTimeout(() => {
                document.getElementById('cell').value = "{{ old('cell') }}";
                document.getElementById('cell').dispatchEvent(new Event('change'));
                @if(old('village'))
                  setTimeout(() => {
                    document.getElementById('village').value = "{{ old('village') }}";
                  }, 100);
                @endif
              }, 100);
            @endif
          }, 100);
        @endif
      }, 100);
    @endif
  @endif
});
</script>

</body>
</html>