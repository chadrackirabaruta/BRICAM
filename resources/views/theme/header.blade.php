<?php 

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive Header</title>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Custom styles for better responsiveness */
        @media (max-width: 576px) {
            .header-nav {
                padding-right: 0.5rem !important;
            }
            .nav-item {
                margin-left: 0.4rem;
            }
            .badge-number {
                font-size: 0.6rem;
                padding: 0.15rem 0.35rem;
            }
            .logo img {
                height: 28px !important;
            }
            .logo span {
                font-size: 0.9rem;
            }
            .toggle-sidebar-btn {
                font-size: 1.6rem !important;
            }
        }

        @media (max-width: 400px) {
            .nav-profile span {
                max-width: 80px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }
    </style>
</head>
<body>
    <!-- ======= Enhanced Responsive Header ======= -->
    <header id="header" class="header fixed-top d-flex align-items-center shadow-sm bg-white py-2 py-md-0">

        <!-- Logo + Sidebar Toggle -->
        <div class="d-flex align-items-center justify-content-start px-3 px-md-4">
            <!-- Logo -->
            <a href="{{ url('/dashboard') }}" class="logo d-flex align-items-center me-3">
                <img src="/assets/img/logo.png" alt="Logo" class="me-2" style="height:35px;">
                <span class="fw-bold text-primary d-sm-inline">BRICAM</span>
            </a>
        <!-- ✅ Toggle Button right after Logo -->
          <i class="bi bi-list toggle-sidebar-btn fs-3 me-0 m-0"></i>
        </div><!-- End Logo + Toggle -->

        <!-- Navbar -->
        <nav class="header-nav ms-auto pe-2 pe-md-3" >
            <ul class="d-flex align-items-center mb-0">

                <!-- Notifications -->
                <li class="nav-item dropdown me-2">
                    <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-bell fs-5"></i>
                        <span class="badge bg-primary badge-number">4</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
                        <li class="dropdown-header">You have 4 new notifications</li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="text-center"><a href="#">View all</a></li>
                    </ul>
                </li>

                <!-- Messages -->
                <li class="nav-item dropdown me-2">
                    <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-chat-left-text fs-5"></i>
                        <span class="badge bg-success badge-number">3</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
                        <li class="dropdown-header">You have 3 new messages</li>
                        <li><hr class="dropdown-divider"></li>
                        <li class="text-center"><a href="#">View all</a></li>
                    </ul>
                </li>

                <!-- Profile -->
                <li class="nav-item dropdown">
                    <a class="nav-link nav-profile d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-2x text-secondary d-none d-md-inline"></i>
                        <i class="fas fa-user-circle text-secondary d-inline d-md-none fs-5"></i>
                       <!-- <span class="dropdown-toggle ps-1 fw-semibold">
                            {{ auth()->user()->name }}
                        </span>-->
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
                        <li class="dropdown-header text-center">
                            <h6 class="fw-bold">{{ auth()->user()->name }}</h6>
                            <span class="text-muted small">{{ auth()->user()->role }}</span>
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
                </li><!-- End Profile Nav -->

            </ul>
        </nav><!-- End Navbar -->

    </header><!-- End Enhanced Header -->

</body>
</html>
