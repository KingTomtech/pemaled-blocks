<aside class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="sidebarMenuLabel">Factory Menu</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <nav class="nav flex-column">
      <!-- Dashboard -->
      <a class="nav-link <?= ($page === 'dashboard') ? 'active' : '' ?>" 
         href="index.php?page=dashboard">
        <i class="fas fa-home me-2"></i> Dashboard
      </a>
      
      <div class="nav-link mb-2">
                <i class="fas fa-language me-2"></i> Translator <div id="google_translate_element" class="d-inline-block align-middle"></div>
            </div>

      <!-- Production -->
      <div class="dropdown mb-2">
        <a class="nav-link dropdown-toggle <?= (in_array($page, ['daily_production', 'production_report', 'targets'])) ? 'active' : '' ?>" 
           href="#" role="button" data-bs-toggle="dropdown">
          <i class="fas fa-industry me-2"></i> Production
        </a>
        <ul class="dropdown-menu">
          <li>
            <a class="dropdown-item <?= ($page === 'daily_production') ? 'active' : '' ?>" 
               href="index.php?page=daily_production">
              Daily Production
            </a>
          </li>
          <li>
            <a class="dropdown-item <?= ($page === 'production_report') ? 'active' : '' ?>" 
               href="index.php?page=production_report">
              Production Reports
            </a>
          </li>
          <li>
            <a class="dropdown-item <?= ($page === 'targets') ? 'active' : '' ?>" 
               href="index.php?page=targets">
              Production Targets
            </a>
          </li>
        </ul>
      </div>

      <!-- Orders & Collections -->
      <div class="dropdown mb-2">
        <a class="nav-link dropdown-toggle <?= (in_array($page, ['orders', 'collections'])) ? 'active' : '' ?>" 
           href="#" role="button" data-bs-toggle="dropdown">
          <i class="fas fa-clipboard-list me-2"></i> Orders
        </a>
        <ul class="dropdown-menu">
          <li>
            <a class="dropdown-item <?= ($page === 'orders') ? 'active' : '' ?>" 
               href="index.php?page=orders">
              Order Management
            </a>
          </li>
          <li>
            <a class="dropdown-item <?= ($page === 'collections') ? 'active' : '' ?>" 
               href="index.php?page=collections">
              Client Collections
            </a>
          </li>
        </ul>
      </div>

      <!-- Inventory -->
      <a class="nav-link <?= ($page === 'materials') ? 'active' : '' ?>" 
         href="index.php?page=materials">
        <i class="fas fa-pallet me-2"></i> Inventory
      </a>

      <!-- Staff Management -->
      <div class="dropdown mb-2">
        <a class="nav-link dropdown-toggle <?= (in_array($page, ['staff', 'attendance', 'payroll'])) ? 'active' : '' ?>" 
           href="#" role="button" data-bs-toggle="dropdown">
          <i class="fas fa-users-cog me-2"></i> Staff
        </a>
        <ul class="dropdown-menu">
          <li>
            <a class="dropdown-item <?= ($page === 'staff') ? 'active' : '' ?>" 
               href="index.php?page=staff">
              Staff Management
            </a>
          </li>
          <li>
            <a class="dropdown-item <?= ($page === 'attendance') ? 'active' : '' ?>" 
               href="index.php?page=attendance">
              Attendance
            </a>
          </li>
          <?php if($_SESSION['role'] === 'admin'): ?>
          <li>
            <a class="dropdown-item <?= ($page === 'payroll') ? 'active' : '' ?>" 
               href="index.php?page=payroll">
              Payroll
            </a>
          </li>
          <?php endif; ?>
        </ul>
      </div>

      <!-- Financial -->
      <?php if($_SESSION['role'] === 'admin'): ?>
      <a class="nav-link <?= ($page === 'transactions') ? 'active' : '' ?>" 
         href="index.php?page=transactions">
        <i class="fas fa-coins me-2"></i> Transactions
      </a>
      <?php endif; ?>

      <!-- Maintenance -->
      <a class="nav-link <?= ($page === 'maintenance') ? 'active' : '' ?>" 
         href="index.php?page=maintenance">
        <i class="fas fa-tools me-2"></i> Maintenance
      </a>

      <!-- Admin Only -->
      <?php if($_SESSION['role'] === 'admin'): ?>
      <div class="dropdown mb-2">
        <a class="nav-link dropdown-toggle <?= ($page === 'users') ? 'active' : '' ?>" 
           href="#" role="button" data-bs-toggle="dropdown">
          <i class="fas fa-user-shield me-2"></i> Administration
        </a>
        <ul class="dropdown-menu">
          <li>
            <a class="dropdown-item <?= ($page === 'users') ? 'active' : '' ?>" 
               href="index.php?page=users">
              User Management
            </a>
          </li>
          <li>
            <a class="dropdown-item <?= ($page === 'schedule') ? 'active' : '' ?>" 
               href="index.php?page=schedule">
              Production Schedule
            </a>
          </li>
        </ul>
      </div>
      <?php endif; ?>

      <!-- Logout -->
      <div class="mt-4 border-top pt-3">
        <a class="nav-link text-danger" href="index.php?page=logout">
          <i class="fas fa-sign-out-alt me-2"></i> Logout
        </a>
      </div>
    </nav>
  </div>
</aside>

<style>
.nav-link {
  transition: all 0.2s ease;
  border-radius: 0.25rem;
}

.nav-link.active {
  background-color: rgba(var(--bs-primary-rgb), 0.1);
  border-left: 3px solid var(--bs-primary);
  font-weight: 500;
}

.dropdown-menu {
  border: none;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
}

.dropdown-item.active {
  background-color: rgba(var(--bs-primary-rgb), 0.1);
  color: var(--bs-primary);
}

@media (max-width: 768px) {
  .offcanvas-start {
    width: 250px;
  }
  .nav-link {
    padding: 0.75rem 1rem;
  }
}
</style>