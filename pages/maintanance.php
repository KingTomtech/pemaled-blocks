<?php
/*require_once '../auth-check.php';

if(!in_array($_SESSION['role'], ['admin', 'manager'])) {
    die("Unauthorized access");
}*/

// Maintenance logging
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $conn->prepare("
        INSERT INTO equipment_maintenance
        (equipment_id, maintenance_date, description, cost)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['equipment_id'],
        $_POST['date'],
        $_POST['description'],
        $_POST['cost']
    ]);
    
    // Update last maintenance date
    $conn->prepare("
        UPDATE equipment 
        SET last_maintenance = ?
        WHERE id = ?
    ")->execute([$_POST['date'], $_POST['equipment_id']]);
    
    header("Location: index.php?pages=maintanance");
    exit();
}

$equipment = $conn->query("SELECT * FROM equipment")->fetchAll();
$maintenance = $conn->query("
    SELECT m.*, e.name 
    FROM equipment_maintenance m
    JOIN equipment e ON m.equipment_id = e.id
    ORDER BY m.maintenance_date DESC
")->fetchAll();
?>

<div class="container-fluid">
    <h3>Equipment Maintenance</h3>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Log Maintenance</div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label>Equipment</label>
                            <select name="equipment_id" class="form-select" required>
                                <?php foreach($equipment as $item): ?>
                                <option value="<?= $item['id'] ?>"><?= $item['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Description</label>
                            <textarea name="description" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label>Cost (ZMW)</label>
                            <input type="number" step="0.01" name="cost" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Log Maintenance</button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Maintenance History</div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Equipment</th>
                                <th>Description</th>
                                <th>Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($maintenance as $log): ?>
                            <tr>
                                <td><?= $log['maintenance_date'] ?></td>
                                <td><?= $log['name'] ?></td>
                                <td><?= $log['description'] ?></td>
                                <td>ZMW <?= number_format($log['cost'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>