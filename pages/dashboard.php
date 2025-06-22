<?php
// pages/dashboard.php
require_once './auth-check.php';
// Check user role
$isAdmin = ($_SESSION['role'] == 'admin');
$isManager = ($_SESSION['role'] == 'manager');
$isAccounts = ($_SESSION['role'] == 'accounts');
$isWorker = ($_SESSION['role'] == 'worker');


// Dashboard Data
$currentDate = date('Y-m-d');
$currentMonth = date('Y-m-01');
$currentYear = date('Y');
$weeklyRate = 35; // Daily wage in ZMW

// 1. Orders Summary
$ordersData = $conn->query("
    SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed_orders
    FROM clients_orders
")->fetch(PDO::FETCH_ASSOC);

// 2. Production Data

$productionData = $conn->query("
    SELECT 
        COALESCE(SUM(CASE WHEN block_type_id = 1 THEN blocks_produced ELSE 0 END), 0) as today_6inch,
        COALESCE(SUM(CASE WHEN block_type_id = 2 THEN blocks_produced ELSE 0 END), 0) as today_4inch,
        (SELECT COALESCE(SUM(blocks_produced), 0) 
         FROM daily_production 
         WHERE YEAR(date) = $currentYear) as yearly_blocks
    FROM daily_production 
    WHERE date = '$currentDate'
")->fetch(PDO::FETCH_ASSOC);

// 3. Materials Status
$materialsData = $conn->query("
    SELECT 
        COUNT(*) AS low_stock,
        (SELECT quantity_remaining FROM materials WHERE material_name = 'Cement') AS cement_stock,
        (SELECT quantity_remaining FROM materials WHERE material_name = 'Stone')  AS stone_stock,
        (SELECT quantity_remaining FROM materials WHERE material_name = 'Diesel') AS diesel_stock,
        (SELECT quantity_remaining FROM materials WHERE material_name = 'Sand') AS sand_stock

    FROM materials 
    WHERE quantity_remaining < 10
")->fetch(PDO::FETCH_ASSOC);


// 4. Financials
$financialData = $conn->query("
    SELECT 
        COALESCE(SUM(CASE WHEN type = 'Income' THEN amount ELSE 0 END), 0) as revenue,
        COALESCE(SUM(CASE WHEN type = 'Expense' THEN amount ELSE 0 END), 0) as expenses
    FROM transactions 
    WHERE date >= '$currentMonth'
")->fetch(PDO::FETCH_ASSOC);

// 5. Staff Tracking
if($isManager || $isAdmin) {
    $attendanceData = $conn->query("
        SELECT 
            COUNT(*) as present_today,
            (SELECT COUNT(*) FROM staff) as total_workers
        FROM staff_daily_log 
        WHERE date = '$currentDate'
    ")->fetch(PDO::FETCH_ASSOC);
}

// 6. Pending Collections
$pendingCollections = $conn->query("
    SELECT COUNT(*) as pending
    FROM clients_orders 
    WHERE status = 'Pending' 
    AND quantity > (
        SELECT COALESCE(SUM(blocks_collected), 0) 
        FROM client_collections 
        WHERE order_id = clients_orders.id
    )
")->fetchColumn();

// Worker-specific data
if ($isWorker) {
    $sql = "
        SELECT 
            COUNT(*) as days_worked,
            (
                SELECT COUNT(*) 
                FROM staff_daily_log 
                WHERE staff_id = ? AND date >= ?
            ) as days_this_week
        FROM staff_daily_log 
        WHERE staff_id = ?
        AND YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)
    ";

    $stmt = $conn->prepare($sql);

    if ($stmt->execute([
        $_SESSION['user_id'],
        date('Y-m-d', strtotime('monday this week')),
        $_SESSION['user_id']
    ])) {
        $workerData = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        // Handle error or fallback data
        $workerData = [
            'days_worked' => 0,
            'days_this_week' => 0
        ];
    }
}

?>

<div class="container-fluid">
    <h2 class="mb-4">Factory Dashboard - <?= date('F j, Y') ?></h2>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <!-- Production Summary -->

        <div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-primary shadow h-100 py-2">
        <div class="card-body">
            <div class="row no-gutters align-items-center">
                <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                        Today's Production
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?= $productionData['today_6inch'] ?> 6" Blocks<br>
                        <?= $productionData['today_4inch'] ?> 4" Blocks
                    </div>
                    <small class="text-muted">
                        Total: <?= $productionData['today_6inch'] + $productionData['today_4inch'] ?> Blocks
                    </small>
                </div>
                <div class="col-auto">
                    <i class="fas fa-industry fa-2x text-gray-300"></i>
                </div>
            </div>
            <?php if($isManager): ?>
                <a href="index.php?page=daily_production" class="btn btn-sm btn-primary mt-2">Update Production</a>
            <?php endif; ?>
        </div>
    </div>
</div>

        <!-- Orders Summary -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Orders Status</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $ordersData['total_orders'] ?> Total
                            </div>
                            <small class="text-muted">
                                <?= $ordersData['pending_orders'] ?> Pending | 
                                <?= $pendingCollections ?> Completed Collections
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <?php if ($isAdmin): ?>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Monthly Financials</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ZMW <?= number_format($financialData['revenue'] - $financialData['expenses'], 2) ?>
                            </div>
                            <small class="text-muted">
                                Income: <?= number_format($financialData['revenue'],2) ?> | 
                                Expenses: <?= number_format($financialData['expenses'],2) ?>
                            </small>
                        </div>
                        <div class="col-auto">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Materials Summary -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Materials Status</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $materialsData['cement_stock'] ?> Bags of Cement
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $materialsData['stone_stock'] ?> Tons of Stone
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <?= $materialsData['diesel_stock'] ?> Litres of Diesel
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= $materialsData['sand_stock'] ?> Tons of Sand
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-boxes fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <?php if($isManager): ?>
                        <a href="index.php?page=materials" class="btn btn-sm btn-warning mt-2">Manage Stock</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Manager Section -->
    <?php if($isManager || $isAdmin): ?>
    <div class="row mb-4">
        <!-- Quick Actions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Manager Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="index.php?page=orders" class="btn btn-primary w-100">
                                <i class="fas fa-truck-loading mr-2"></i> Record Collections
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="index.php?page=materials" class="btn btn-warning w-100">
                                <i class="fas fa-boxes mr-2"></i> Stock Materials
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="index.php?page=attendance" class="btn btn-info w-100">
                                <i class="fas fa-clipboard-check mr-2"></i> Staff Attendance
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="index.php?page=production_report" class="btn btn-success w-100">
                                <i class="fas fa-chart-line mr-2"></i> Production Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Attendance -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Today's Attendance (<?= $attendanceData['present_today'] ?? 0 ?>/<?= $attendanceData['total_workers'] ?? 0 ?>)
                    </h6>
                    <a href="index.php?page=attendance" class="btn btn-sm btn-primary">Manage</a>
                </div>
                <div class="card-body">
                    <?php if($isAdmin): ?>
                        <a href="index.php?page=payroll" class="btn btn-success w-100">
                            <i class="fas fa-money-bill-wave mr-2"></i> Process Weekly Payroll
                        </a>
                    <?php else: ?>
                        <p class="mb-0">Next Payroll: <?= date('l, F j', strtotime('next monday')) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Recent Activity -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
                    <a href="index.php?page=orders" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Type</th>
                                    <th>Qty</th>
                                    <th>Collected</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recentOrders = $conn->query("
                                    SELECT o.*, bt.type_name, 
                                        (SELECT SUM(blocks_collected) FROM client_collections 
                                         WHERE order_id = o.id) AS collected
                                    FROM clients_orders o
                                    JOIN block_types bt ON o.block_type_id = bt.id
                                    ORDER BY o.order_date DESC LIMIT 5
                                ");
                                
                                while ($order = $recentOrders->fetch(PDO::FETCH_ASSOC)):
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($order['client_name']) ?></td>
                                    <td><?= $order['type_name'] ?></td>
                                    <td><?= $order['quantity'] ?></td>
                                    <td><?= $order['collected'] ?? 0 ?></td>
                                    <td>
                                        <span class="badge bg-<?= 
                                            ($order['status'] == 'Completed') ? 'success' : 
                                            (($order['status'] == 'Pending') ? 'warning' : 'danger') ?>">
                                            <?= $order['status'] ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Collections -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Collections</h6>
                    <a href="index.php?page=orders" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Blocks</th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $recentCollections = $conn->query("
                                    SELECT c.*, o.client_name, bt.type_name, bt.unit_price
                                    FROM client_collections c
                                    JOIN clients_orders o ON c.order_id = o.id
                                    JOIN block_types bt ON o.block_type_id = bt.id
                                    ORDER BY c.date DESC LIMIT 5
                                ");
                                
                                while ($collection = $recentCollections->fetch(PDO::FETCH_ASSOC)):
                                ?>
                                <tr>
                                    <td><?= date('m/d', strtotime($collection['date'])) ?></td>
                                    <td><?= htmlspecialchars($collection['client_name']) ?></td>
                                    <td><?= $collection['blocks_collected'] ?></td>
                                    
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Creation (Manager/Admin Only) -->
    <?php if($isAdmin || $isManager): ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Create New Order</h6>
        </div>
        <div class="card-body">
            <form action="pages/create_order.php" method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Client Name</label>
                        <input type="text" class="form-control" name="client_name" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" name="contact" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Block Type</label>
                        <select class="form-select" name="block_type_id" required>
                            <?php 
                            $blockTypes = $conn->query("SELECT * FROM block_types");
                            while($type = $blockTypes->fetch()): ?>
                            <option value="<?= $type['id'] ?>">
                                <?= $type['type_name'] ?> (ZMW <?= number_format($type['unit_price'],2) ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" min="1" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Order Date</label>
                        <input type="date" class="form-control" name="order_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-2"></i> Create Order
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Worker Section -->
    <?php if($isWorker): ?>
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Worker Overview</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card border-left-success h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Days Worked This Week</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?= $workerData['days_this_week'] ?? 0 ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card border-left-info h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Expected Weekly Pay</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        ZMW <?= number_format(($workerData['days_this_week'] ?? 0) * $weeklyRate, 2) ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>