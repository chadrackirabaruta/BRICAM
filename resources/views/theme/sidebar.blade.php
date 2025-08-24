<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">

  <ul class="sidebar-nav" id="sidebar-nav">

    <!-- Dashboard -->
  <li class="nav-item">
  <a class="nav-link d-flex align-items-center gap-2 py-2 px-3 rounded {{ request()->is('dashboard') ? 'active bg-primary text-white' : 'text-dark' }}" 
     href="{{ url('/dashboard') }}" 
     style="transition: 0.3s ease;">
    <i class="bi bi-grid {{ request()->is('dashboard') ? 'text-white' : 'text-primary' }}"></i>
    <span class="fw-semibold">Dashboard</span>
  </a>
</li>


    <!-- Categories -->
    @if(auth()->user()->role === 'admin')
      <li class="nav-item">
        <a class="nav-link collapsed {{ request()->routeIs('categories.*') ? 'active' : '' }}" data-bs-target="#categories-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-tags text-success"></i><span>Categories</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="categories-nav" class="nav-content collapse {{ request()->routeIs('categories.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.index') ? 'active' : '' }}">
              <i class="bi bi-list"></i><span>Manage Categories</span>
            </a>
          </li>
        </ul>
      </li>
    @endif

    <!-- Employees -->
    @if(auth()->user()->role === 'admin')
      <li class="nav-item">
        <a class="nav-link collapsed {{ request()->routeIs('employees.*') ? 'active' : '' }}" data-bs-target="#employees-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-people text-warning"></i><span>Employees</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="employees-nav" class="nav-content collapse {{ request()->routeIs('employees.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('employees.create') }}" class="{{ request()->routeIs('employees.create') ? 'active' : '' }}">
              <i class="bi bi-plus-circle"></i><span>New Employee</span>
            </a>
          </li>
          <li>
            <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.index') ? 'active' : '' }}">
              <i class="bi bi-list"></i><span>All Employees</span>
            </a>
          </li>
        </ul>
      </li>
    @endif

    <!-- Customers -->
    <li class="nav-item">
      <a class="nav-link collapsed {{ request()->routeIs('customers.*') ? 'active' : '' }}" data-bs-target="#customers-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-person-lines-fill text-info"></i><span>Customers</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="customers-nav" class="nav-content collapse {{ request()->routeIs('customers.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
        <li>
          <a href="{{ route('customers.create') }}" class="{{ request()->routeIs('customers.create') ? 'active' : '' }}">
            <i class="bi bi-plus-circle"></i><span>New Customer</span>
          </a>
        </li>
        <li>
          <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.index') ? 'active' : '' }}">
            <i class="bi bi-list"></i><span>All Customers</span>
          </a>
        </li>
      </ul>
    </li>

    <!-- Production -->
    @if(auth()->user()->role === 'admin')
      <li class="nav-item">
        <a class="nav-link collapsed {{ request()->routeIs('productions.*') || request()->routeIs('stock_types.*') ? 'active' : '' }}" data-bs-target="#production-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-hammer text-danger"></i><span>Production</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="production-nav" class="nav-content collapse {{ request()->routeIs('productions.*') || request()->routeIs('stock_types.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('stock_types.index') }}" class="{{ request()->routeIs('stock_types.*') ? 'active' : '' }}">
              <i class="bi bi-diagram-3"></i><span>Manage Stages</span>
            </a>
          </li>
          <li>
            <a href="{{ route('productions.index') }}" class="{{ request()->routeIs('productions.index') ? 'active' : '' }}">
              <i class="bi bi-list-task"></i><span>Manage Production</span>
            </a>
          </li>
          <li>
            <a href="{{ route('productions.summary') }}" class="{{ request()->routeIs('productions.summary') ? 'active' : '' }}">
              <i class="bi bi-list"></i><span>Production Summary</span>
            </a>
          </li>
          <li>
            <a href="{{ route('productions.report') }}" class="{{ request()->routeIs('productions.report') ? 'active' : '' }}">
              <i class="bi bi-bar-chart"></i><span>Production Report</span>
            </a>
          </li>
        </ul>
      </li>
    @endif

    <!-- Transport -->
    @if(auth()->user()->role === 'admin')
      <li class="nav-item">
        <a class="nav-link collapsed {{ request()->routeIs('transport-records.*') ? 'active' : '' }}" data-bs-target="#transport-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-truck text-purple"></i><span>Transport</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="transport-nav" class="nav-content collapse {{ request()->routeIs('transport-records.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('transport-records.index') }}" class="{{ request()->routeIs('transport-records.index') ? 'active' : '' }}">
              <i class="bi bi-list"></i><span>Manage Transport</span>
            </a>
          </li>
        </ul>
      </li>
    @endif

    <!-- Sales -->
    @if(auth()->user()->role === 'admin')
      <li class="nav-item">
        <a class="nav-link collapsed {{ request()->routeIs('sales.*') ? 'active' : '' }}" data-bs-target="#sales-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-currency-dollar text-success"></i><span>Sales</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="sales-nav" class="nav-content collapse {{ request()->routeIs('sales.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('sales.create') }}" class="{{ request()->routeIs('sales.create') ? 'active' : '' }}">
              <i class="bi bi-shop"></i><span>New Sale / POS</span>
            </a>
          </li>
          <li>
            <a href="{{ route('sales.index') }}" class="{{ request()->routeIs('sales.index') ? 'active' : '' }}">
              <i class="bi bi-list"></i><span>All Sales</span>
            </a>
          </li>
        </ul>
      </li>
    @endif



   <!-- Payroll -->
@if(auth()->user()->role === 'admin')
  <li class="nav-item">
    <a class="nav-link collapsed {{ request()->routeIs('salary.*') || request()->routeIs('payroll.wages.index') ? 'active' : '' }}" 
       data-bs-target="#payroll-nav" data-bs-toggle="collapse" href="#">
      <i class="bi bi-cash-coin text-warning"></i><span>Payroll</span>
      <i class="bi bi-chevron-down ms-auto"></i>
    </a>
    <ul id="payroll-nav" class="nav-content collapse {{ request()->routeIs('salary.*') || request()->routeIs('payroll.wages.index') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">

       <!-- Manage Wages -->
      <li>
        <a href="{{ route('payroll.wages.index') }}" class="{{ request()->routeIs('payroll.wages.index') ? 'active' : '' }}">
          <i class="bi bi-calculator-fill text-success"></i><span>Manage Wages</span>
        </a>
      </li>
      <!-- All Salaries -->
      <li>
        <a href="{{ route('salary.All') }}" class="{{ request()->routeIs('salary.All') ? 'active' : '' }}">
          <i class="bi bi-people-fill text-info"></i><span>All Salaries</span>
        </a>
      </li>

    </ul>
  </li>
@endif

    <!-- Reports -->
    @if(auth()->user()->role === 'admin')
      <li class="nav-item">
        <a class="nav-link collapsed {{ request()->routeIs('reports.*') ? 'active' : '' }}" data-bs-target="#reports-nav" data-bs-toggle="collapse" href="#">
          <i class="bi bi-bar-chart text-danger"></i><span>Reports</span><i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="reports-nav" class="nav-content collapse {{ request()->routeIs('reports.*') ? 'show' : '' }}" data-bs-parent="#sidebar-nav">
          <li>
            <a href="{{ route('reports.stock-summary') }}" class="{{ request()->routeIs('reports.stock-summary') ? 'active' : '' }}">
              <i class="bi bi-bar-chart"></i><span>Stock Summary Report</span>
            </a>
          </li>
          <li>
            <a href="{{ route('reports.sales') }}" class="{{ request()->routeIs('reports.sales') ? 'active' : '' }}">
              <i class="bi bi-bar-chart"></i><span>Sales Report</span>
            </a>
          </li>
        </ul>
      </li>
    @endif
  </ul>
</aside><!-- End Sidebar -->
