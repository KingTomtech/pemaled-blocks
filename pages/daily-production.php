<?php
require_once 'config.php';
/*//require_once 'header.php';
require_once 'auth_check.php';

// Restrict to managers/admins
if(!in_array($_SESSION['role'], ['admin', 'manager'])) {
    header("Location: dashboard.php");
    exit();
}*/

$today = date('Y-m-d');
$cementStock = $conn->query("SELECT quantity_remaining FROM materials WHERE material_name = 'Cement'")->fetchColumn();

if(isset($_POST['save_production'])) {
    try {
        $conn->beginTransaction();
        
        // Insert production record
        $stmt = $conn->prepare("
            INSERT INTO daily_production 
            (date, block_type_id, blocks_produced, cement_bags_used, fuel_liters) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['production_date'],
            $_POST['block_type_id'],
            $_POST['blocks_produced'],
            $_POST['cement_bags_used'],
            $_POST['fuel_liters'] ?? 0
        ]);
        
        // Update cement stock
        $conn->prepare("
            UPDATE materials SET 
            quantity_remaining = quantity_remaining - ?,
            last_updated = CURDATE()
            WHERE material_name = 'Cement'
        ")->execute([$_POST['cement_bags_used']]);
        
        $conn->commit();
        header("Location: index.php?page=dashboard");
        exit();
    } catch(PDOException $e) {
        $conn->rollBack();
        $error = "Error recording production: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4>Daily Production Log</h4>
            <p class="mb-0">Cement Available: <?= $cementStock ?> bags</p>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger m-3"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success m-3">Production recorded successfully!</div>
        <?php endif; ?>

        <form method="post">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" name="production_date" 
                               value="<?= $today ?>" class="form-control" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Block Type</label>
                        <select name="block_type_id" class="form-select" required>
                            <?php 
                            $blockTypes = $conn->query("SELECT * FROM block_types");
                            while($type = $blockTypes->fetch()): ?>
                            <option value="<?= $type['id'] ?>">
                                <?= $type['type_name'] ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Blocks Produced</label>
                        <input type="number" name="blocks_produced" 
                               class="form-control" min="1" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Cement Bags Used</label>
                        <input type="number" name="cement_bags_used" 
                               class="form-control" max="<?= $cementStock ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label">Fuel Used (Liters)</label>
                        <input type="number" step="0.1" name="fuel_liters" 
                               class="form-control">
                    </div>
                </div>
                
                <button type="submit" name="save_production" 
                        class="btn btn-primary mt-3">
                    Save Production Data
                </button>
            </div>
        </form>
        
        <div class="card-footer">
            <h5>Today's Production Summary</h5>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Block Type</th>
                        <th>Quantity</th>
                        <th>Cement Used</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $todayProduction = $conn->query("
                        SELECT bt.type_name, SUM(dp.blocks_produced) as total_blocks,
                               SUM(dp.cement_bags_used) as total_cement
                        FROM daily_production dp
                        JOIN block_types bt ON dp.block_type_id = bt.id
                        WHERE dp.date = '$today'
                        GROUP BY dp.block_type_id
                    ");
                    
                    while($prod = $todayProduction->fetch()): ?>
                    <tr>
                        <td><?= $prod['type_name'] ?></td>
                        <td><?= $prod['total_blocks'] ?></td>
                        <td><?= $prod['total_cement'] ?> bags</td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

