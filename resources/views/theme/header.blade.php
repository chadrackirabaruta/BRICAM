<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>
<!-- ======= Header ======= -->
<header id="header" class="header fixed-top d-flex align-items-center shadow-sm">

  <div class="d-flex align-items-center justify-content-between w-100 px-3">

    <!-- Logo -->
    <a href="#" class="logo d-flex align-items-center">
      <img src="/assets/img/logo.png" alt="Logo" class="img-fluid" style="max-height: 40px;">
      <span class="d-none d-lg-block ms-2 fw-bold">BRICAM</span>
    </a>

    <!-- Sidebar Toggle (only mobile) -->
    <button class="btn btn-link p-0 d-lg-none toggle-sidebar-btn">
      <i class="bi bi-list fs-2"></i>
    </button>

    <!-- Navigation -->
    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center mb-0">

        <!-- Notifications -->
        <li class="nav-item dropdown">
          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">4</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">You have 4 new notifications</li>
            <li><hr class="dropdown-divider"></li>
            <!-- Example notification -->
            <li class="notification-item">
              <i class="bi bi-info-circle text-primary"></i>
              <div>
                <h6>System Update</h6>
                <p>New updates available</p>
              </div>
            </li>
          </ul>
        </li>

        <!-- Messages -->
        <li class="nav-item dropdown">
          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-chat-left-text"></i>
            <span class="badge bg-success badge-number">3</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
            <li class="dropdown-header">You have 3 new messages</li>
            <li><hr class="dropdown-divider"></li>
            <!-- Example message -->
            <li class="message-item d-flex align-items-start">
              <i class="bi bi-person-circle text-secondary fs-4 me-2"></i>
              <div>
                <h6>John Doe</h6>
                <p>Hey, are you available?</p>
              </div>
            </li>
          </ul>
        </li>

        <!-- Profile -->
        <li class="nav-item dropdown pe-2">
          <a class="nav-link nav-profile d-flex align-items-center" href="#" data-bs-toggle="dropdown">
            <i class="fas fa-user-circle fa-2x me-1"></i>
            <!-- ✅ Always visible on all screens -->
            <span class="dropdown-toggle ps-1">{{ auth()->user()->name }}</span>
          </a>

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header text-center">
              <h6 class="mb-0">{{ auth()->user()->name }}</h6>
              <small class="text-muted">{{ auth()->user()->role }}</small>
            </li>
            <li><hr class="dropdown-divider"></li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="{{ route('profile') }}">
                <i class="bi bi-person me-2"></i>
                <span>My Profile</span>
              </a>
            </li>

            <li><hr class="dropdown-divider"></li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="#"
                 onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="bi bi-box-arrow-right me-2"></i>
                <span>Sign Out</span>
              </a>
              <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
              </form>
            </li>
          </ul>
        </li>

      </ul>
    </nav><!-- End Icons Navigation -->

  </div>
</header><!-- End Header -->
