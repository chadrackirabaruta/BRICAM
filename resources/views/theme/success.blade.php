{{-- Success Message --}}
@if (session('success'))
  <div id="success-message" class="alert alert-success alert-dismissible fade show" style="margin-left: 20px;">
    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

{{-- Error Message --}}
@if (session('error'))
  <div id="danger-message" class="alert alert-danger alert-dismissible fade show" style="margin-left: 20px;">
    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

{{-- Validation Errors --}}
@if ($errors->any())
  <div id="danger-message" class="alert alert-danger" style="margin-left:20px;">
    <ul class="mb-0">
      @foreach ($errors->all() as $error)
        <li class="text-danger"> {{ $error }} </li>
      @endforeach
    </ul>
  </div>
@endif

{{-- Auto-hide flash messages after 7 seconds --}}
<script>
  setTimeout(function () {
    document.querySelectorAll('#success-message, #danger-message').forEach(function (element) {
      element.style.display = 'none';
    });
  }, 7000);
</script>