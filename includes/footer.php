<!-- Footer -->
<footer class="custom-header-footer fixed-bottom">
    <div class="container h-100">
        <div class="d-flex justify-content-around align-items-center h-100 px-4">
            <a href="index.php?page=dashboard" 
               class="nav-icon <?= ($page === 'dashboard') ? 'active-nav' : '' ?>" 
               role="button" 
               tabindex="0"
               aria-label="Navigate to Dashboard">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>

            <a href="index.php?page=orders" 
               class="nav-icon <?= ($page === 'orders') ? 'active-nav' : '' ?>" 
               role="button" 
               tabindex="0"
               aria-label="Navigate to Orders">
                <i class="fas fa-clipboard-list"></i>
                <span>Orders</span>
            </a>

            <a href="index.php?page=materials" 
               class="nav-icon <?= ($page === 'materials') ? 'active-nav' : '' ?>" 
               role="button" 
               tabindex="0"
               aria-label="Navigate to Inventory">
                <i class="fas fa-warehouse"></i>
                <span>Stock</span>
            </a>

            <a href="index.php?page=production_report" 
               class="nav-icon <?= ($page === 'production_report') ? 'active-nav' : '' ?>" 
               role="button" 
               tabindex="0"
               aria-label="Navigate to Reports">
                <i class="fas fa-chart-pie"></i>
                <span>Reports</span>
            </a>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/script.js"></script>
</body>
</html>