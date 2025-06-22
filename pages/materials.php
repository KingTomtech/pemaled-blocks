<?php
require_once("config.php");

// Handle all form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Material Management
        if (isset($_POST['add_material'])) {
            $stmt = $conn->prepare("INSERT INTO materials (material_name, quantity_remaining, unit) 
                                  VALUES (:name, :quantity, :unit)");
            $stmt->execute([
                ':name' => $_POST['material_name'],
                ':quantity' => (float)$_POST['quantity_remaining'],
                ':unit' => $_POST['unit']
            ]);
            $success = "Material added successfully!";
        }
        
        if (isset($_POST['update_material'])) {
            $stmt = $conn->prepare("UPDATE materials SET 
                                  material_name = :name,
                                  quantity_remaining = :quantity,
                                  unit = :unit
                                  WHERE id = :id");
            $stmt->execute([
                ':name' => $_POST['material_name'],
                ':quantity' => (float)$_POST['quantity_remaining'],
                ':unit' => $_POST['unit'],
                ':id' => $_POST['material_id']
            ]);
            $success = "Material updated successfully!";
        }

        // Production Management
        if (isset($_POST['add_production'])) {
            // Insert production record
            $stmt = $conn->prepare("INSERT INTO daily_production 
                                  (block_type_id, blocks_produced, cement_bags_used, fuel_liters, waste_blocks)
                                  VALUES (:block_type, :quantity, :cement, :fuel, :waste)");
            $stmt->execute([
                ':block_type' => $_POST['block_type'],
                ':quantity' => $_POST['quantity'],
                ':cement' => $_POST['cement_used'],
                ':fuel' => $_POST['fuel_used'],
                ':waste' => $_POST['waste']
            ]);
            
            // Update material usage
            $production_id = $conn->lastInsertId();
            
            // Record cement usage
            $stmt = $conn->prepare("INSERT INTO material_usage (production_id, material_id, quantity_used)
                                  VALUES (:prod_id, 1, :cement)");
            $stmt->execute([':prod_id' => $production_id, ':cement' => $_POST['cement_used']]);
            
            // Record fuel usage
            $stmt = $conn->prepare("INSERT INTO material_usage (production_id, material_id, quantity_used)
                                  VALUES (:prod_id, 3, :fuel)");
            $stmt->execute([':prod_id' => $production_id, ':fuel' => $_POST['fuel_used']]);
            
            $success = "Production recorded successfully!";
        }
        
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}

// Handle deletions
if (isset($_GET['delete'])) {
    try {
        $stmt = $conn->prepare("DELETE FROM materials WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $success = "Material deleted successfully!";
    } catch (PDOException $e) {
        $error = "Delete failed: " . $e->getMessage();
    }
}

// Export data
if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="factory_data_'.date('Y-m-d').'.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Materials section
    fputcsv($output, ['Materials Inventory']);
    fputcsv($output, ['ID', 'Material', 'Stock', 'Unit']);
    $materials = $conn->query("SELECT * FROM materials");
    while ($mat = $materials->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, $mat);
    }
    
    // Blocks section
    fputcsv($output, []);
    fputcsv($output, ['Block Inventory']);
    fputcsv($output, ['Type', 'Available Stock', 'Unit Price', 'Stock Value']);
    $blocks = $conn->query("
        SELECT bt.type_name, 
               SUM(dp.blocks_produced) - SUM(dp.waste_blocks) - 
               COALESCE((SELECT SUM(cc.blocks_collected) 
                       FROM client_collections cc 
                       WHERE cc.order_id IN (SELECT id FROM clients_orders WHERE block_type_id = bt.id)), 0) AS stock,
               bt.unit_price
        FROM block_types bt
        LEFT JOIN daily_production dp ON bt.id = dp.block_type_id
        GROUP BY bt.id
    ");
    while ($block = $blocks->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $block['type_name'],
            $block['stock'] ?? 0,
            $block['unit_price'],
            ($block['stock'] ?? 0) * $block['unit_price']
        ]);
    }
    
    fclose($output);
    exit;
}

// Get all data
$materials = $conn->query("SELECT * FROM materials ORDER BY material_name");
$block_types = $conn->query("SELECT * FROM block_types");
$production_log = $conn->query("
    SELECT dp.*, bt.type_name 
    FROM daily_production dp
    JOIN block_types bt ON dp.block_type_id = bt.id
    ORDER BY dp.date DESC LIMIT 10
");

// Calculate block stock
$block_stock = $conn->query("
    SELECT bt.id, bt.type_name, bt.unit_price,
           COALESCE(SUM(dp.blocks_produced), 0) - 
           COALESCE(SUM(dp.waste_blocks), 0) - 
           COALESCE((SELECT SUM(cc.blocks_collected) 
                   FROM client_collections cc 
                   JOIN clients_orders co ON cc.order_id = co.id 
                   WHERE co.block_type_id = bt.id), 0) AS available
    FROM block_types bt
    LEFT JOIN daily_production dp ON bt.id = dp.block_type_id
    GROUP BY bt.id
")->fetchAll(PDO::FETCH_OBJ);
?>
 <style>
        .card { margin-bottom: 20px; }
        .table-danger { background-color: #ffe6e6; }
        .stock-card { background: #f8f9fa; padding: 15px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <!-- Materials Section -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Raw Materials Inventory</h4>
                <div>
                    <a href="index.php?page=materials&export=csv" class="btn btn-success btn-sm">
                        <i class="fas fa-file-export"></i> Export CSV
                    </a>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#materialModal">
                        <i class="fas fa-plus"></i> Add Material
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Material</th>
                            <th>Stock</th>
                            <th>Unit</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($mat = $materials->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr class="<?= $mat['quantity_remaining'] < 10 ? 'table-danger' : '' ?>">
                                <td><?= htmlspecialchars($mat['material_name']) ?></td>
                                <td><?= number_format($mat['quantity_remaining'], ) ?></td>
                                <td><?= htmlspecialchars($mat['unit']) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-btn" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#materialModal"
                                            data-id="<?= $mat['id'] ?>"
                                            data-name="<?= htmlspecialchars($mat['material_name']) ?>"
                                            data-quantity="<?= $mat['quantity_remaining'] ?>"
                                            data-unit="<?= htmlspecialchars($mat['unit']) ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Production Section -->
        <div class="card">
            <div class="card-header">
                <h4>Production Management</h4>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Production Form -->
                    <div class="col-md-6">
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Block Type</label>
                                    <select name="block_type" class="form-select" required>
                                        <?php foreach ($block_types->fetchAll() as $type): ?>
                                            <option value="<?= $type['id'] ?>"><?= $type['type_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Quantity Produced</label>
                                    <input type="number" name="quantity" class="form-control" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Waste Blocks</label>
                                    <input type="number" name="waste" class="form-control" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Cement Used (bags)</label>
                                    <input type="number" step="0.1" name="cement_used" class="form-control" required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">Fuel Used (liters)</label>
                                    <input type="number" step="0.1" name="fuel_used" class="form-control" required>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" name="add_production" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Record Production
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Block Inventory -->
                    <div class="col-md-6">
                        <div class="stock-card">
                            <h5>Current Block Stock</h5>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Type</th>
                                        <th>Available</th>
                                        <th>Unit Price</th>
                                        <th>Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($block_stock as $block): ?>
                                        <tr class="<?= $block->available < 500 ? 'table-danger' : '' ?>">
                                            <td><?= $block->type_name ?></td>
                                            <td><?= number_format($block->available ?? 0) ?></td>
                                            <td>ZMk <?= number_format($block->unit_price, 2) ?></td>
                                            <td>ZMk <?= number_format(($block->available ?? 0) * $block->unit_price, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Production Log -->
        <div class="card">
            <div class="card-header">
                <h4>Recent Production Entries</h4>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Block Type</th>
                            <th>Produced</th>
                            <th>Waste</th>
                            <th>Cement Used</th>
                            <th>Fuel Used</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($entry = $production_log->fetch(PDO::FETCH_ASSOC)): ?>
                            <tr>
                                <td><?= $entry['date'] ?></td>
                                <td><?= $entry['type_name'] ?></td>
                                <td><?= number_format($entry['blocks_produced']) ?></td>
                                <td><?= number_format($entry['waste_blocks']) ?></td>
                                <td><?= number_format($entry['cement_bags_used']) ?> bags</td>
                                <td><?= number_format($entry['fuel_liters'], 1) ?>L</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Material Modal -->
        <div class="modal fade" id="materialModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title">Manage Material</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="material_id" id="materialId">
                            <div class="mb-3">
                                <label class="form-label">Material Name</label>
                                <input type="text" class="form-control" name="material_name" id="materialName" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" step="0.01" class="form-control" name="quantity_remaining" id="materialQuantity" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Unit</label>
                                <select class="form-select" name="unit" id="materialUnit" required>
                                    <option value="bags">Bags</option>
                                    <option value="tons">Tons</option>
                                    <option value="liters">Liters</option>
                                    <option value="units">Units</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" name="add_material" id="modalSubmit">
                                Add Material
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Material Modal Handling
    document.addEventListener('DOMContentLoaded', function() {
        const modal = new bootstrap.Modal('#materialModal');
        const editButtons = document.querySelectorAll('.edit-btn');

        editButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('materialId').value = btn.dataset.id;
                document.getElementById('materialName').value = btn.dataset.name;
                document.getElementById('materialQuantity').value = btn.dataset.quantity;
                document.getElementById('materialUnit').value = btn.dataset.unit;
                document.getElementById('modalSubmit').name = 'update_material';
                document.querySelector('.modal-title').textContent = 'Edit Material';
            });
        });

        document.getElementById('materialModal').addEventListener('hidden.bs.modal', () => {
            document.getElementById('materialId').value = '';
            document.getElementById('materialName').value = '';
            document.getElementById('materialQuantity').value = '';
            document.getElementById('materialUnit').value = 'bags';
            document.getElementById('modalSubmit').name = 'add_material';
            document.querySelector('.modal-title').textContent = 'Add Material';
        });
    });
    </script>
</body>
</html>