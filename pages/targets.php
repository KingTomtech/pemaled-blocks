<?php
/*require_once '../auth-check.php';

if(!in_array($_SESSION['role'], ['admin', 'manager'])) {
    die("Unauthorized access");
}*/

// Target creation
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("
        INSERT INTO production_targets 
        (target_date, block_type_id, target_quantity)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([
        $_POST['target_date'],
        $_POST['block_type_id'],
        $_POST['target_quantity']
    ]);
    header("Location: index.php?pages=targets");
    exit();
}

// Fetch data
$targets = $conn->query("
    SELECT t.*, bt.type_name,
           (SELECT SUM(blocks_produced) 
            FROM daily_production 
            WHERE block_type_id = t.block_type_id
            AND date = t.target_date) as actual
    FROM production_targets t
    JOIN block_types bt ON t.block_type_id = bt.id
    ORDER BY target_date DESC
")->fetchAll();

$blockTypes = $conn->query("SELECT * FROM block_types")->fetchAll();
?>

<div class="container-fluid">
    <h3>Production Targets</h3>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <canvas id="targetsChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Set New Target</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label>Target Date</label>
                            <input type="date" name="target_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Block Type</label>
                            <select name="block_type_id" class="form-select" required>
                                <?php foreach($blockTypes as $type): ?>
                                <option value="<?= $type['id'] ?>"><?= $type['type_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Target Quantity</label>
                            <input type="number" name="target_quantity" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Set Target</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Block Type</th>
                        <th>Target</th>
                        <th>Actual</th>
                        <th>Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($targets as $target): 
                        $progress = ($target['actual'] / $target['target_quantity']) * 100;
                    ?>
                    <tr>
                        <td><?= $target['target_date'] ?></td>
                        <td><?= $target['type_name'] ?></td>
                        <td><?= $target['target_quantity'] ?></td>
                        <td><?= $target['actual'] ?? 0 ?></td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?= $progress ?>%">
                                    <?= round($progress) ?>%
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
new Chart(document.getElementById('targetsChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($targets, 'target_date')) ?>,
        datasets: [{
            label: 'Target',
            data: <?= json_encode(array_column($targets, 'target_quantity')) ?>,
            backgroundColor: '#ff6384'
        }, {
            label: 'Actual',
            data: <?= json_encode(array_column($targets, 'actual')) ?>,
            backgroundColor: '#36a2eb'
        }]
    }
});
</script>