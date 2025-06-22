<?php
/*require_once '../auth-check.php';

// Restrict to managers/admins
if(!in_array($_SESSION['role'], ['admin', 'manager'])) {
    header("Location: index.php?page=dashboard");
    exit();
}*/

// Date filters
$startDate = $_GET['start'] ?? date('Y-m-01');
$endDate = $_GET['end'] ?? date('Y-m-t');
$blockType = $_GET['block_type'] ?? 'all';

// Base query
$query = "SELECT dp.date, bt.type_name, 
          SUM(dp.blocks_produced) as total_produced,
          SUM(dp.waste_blocks) as total_waste,
          SUM(dp.production_cost) as total_cost,
          GROUP_CONCAT(m.material_name SEPARATOR ', ') as materials_used
          FROM daily_production dp
          JOIN block_types bt ON dp.block_type_id = bt.id
          LEFT JOIN material_usage mu ON dp.id = mu.production_id
          LEFT JOIN materials m ON mu.material_id = m.id
          WHERE dp.date BETWEEN ? AND ?";

$params = [$startDate, $endDate];

if($blockType !== 'all') {
    $query .= " AND dp.block_type_id = ?";
    $params[] = $blockType;
}

$query .= " GROUP BY dp.date, dp.block_type_id ORDER BY dp.date DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$productionData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get block types for filter
$blockTypes = $conn->query("SELECT * FROM block_types")->fetchAll();
?>

<div class="container-fluid">
    <h3 class="mb-4">Production Report</h3>
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="index.php">
                <input type="hidden" name="page" value="production_report">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label>Start Date</label>
                        <input type="date" name="start" class="form-control" value="<?= $startDate ?>">
                    </div>
                    <div class="col-md-3">
                        <label>End Date</label>
                        <input type="date" name="end" class="form-control" value="<?= $endDate ?>">
                    </div>
                    <div class="col-md-3">
                        <label>Block Type</label>
                        <select name="block_type" class="form-select">
                            <option value="all">All Types</option>
                            <?php foreach($blockTypes as $type): ?>
                            <option value="<?= $type['id'] ?>" <?= $blockType == $type['id'] ? 'selected' : '' ?>>
                                <?= $type['type_name'] ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">Generate Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5>Total Produced</h5>
                    <h2 class="text-success">
                        <?= array_sum(array_column($productionData, 'total_produced')) ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h5>Total Waste</h5>
                    <h2 class="text-danger">
                        <?= array_sum(array_column($productionData, 'total_waste')) ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h5>Total Cost</h5>
                    <h2 class="text-warning">
                        ZMW <?= number_format(array_sum(array_column($productionData, 'total_cost')), 2) ?>
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h5>Avg Daily Production</h5>
                    <h2 class="text-info">
                        <?= count($productionData) > 0 ? 
                           round(array_sum(array_column($productionData, 'total_produced')) / count($productionData)) : 0 ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Report -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Production Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Block Type</th>
                            <th>Produced</th>
                            <th>Waste</th>
                            <th>Cost</th>
                            <th>Materials Used</th>
                            <th>Efficiency</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($productionData as $row): 
                            $efficiency = ($row['total_produced'] / 
                                        ($row['total_produced'] + $row['total_waste'])) * 100;
                        ?>
                        <tr>
                            <td><?= date('M j, Y', strtotime($row['date'])) ?></td>
                            <td><?= $row['type_name'] ?></td>
                            <td><?= $row['total_produced'] ?></td>
                            <td class="<?= $row['total_waste'] > 0 ? 'text-danger' : '' ?>">
                                <?= $row['total_waste'] ?>
                            </td>
                            <td>ZMW <?= number_format($row['total_cost'], 2) ?></td>
                            <td><?= $row['materials_used'] ?></td>
                            <td class="<?= $efficiency < 90 ? 'text-danger' : 'text-success' ?>">
                                <?= number_format($efficiency, 1) ?>%
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <canvas id="productionTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <canvas id="wasteAnalysisChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Production Trend Chart
new Chart(document.getElementById('productionTrendChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($productionData, 'date')) ?>,
        datasets: [{
            label: 'Blocks Produced',
            data: <?= json_encode(array_column($productionData, 'total_produced')) ?>,
            borderColor: '#28a745',
            tension: 0.1
        }]
    }
});

// Waste Analysis Chart
new Chart(document.getElementById('wasteAnalysisChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($productionData, 'date')) ?>,
        datasets: [{
            label: 'Waste Blocks',
            data: <?= json_encode(array_column($productionData, 'total_waste')) ?>,
            backgroundColor: '#dc3545'
        }]
    }
});
</script>