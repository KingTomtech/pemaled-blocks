<?php
// pages/orders.php
require_once("./config.php");

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
      // Create New Order
      if (isset($_POST['add_order'])) {
          $stmt = $conn->prepare("INSERT INTO clients_orders 
              (client_name, contact, block_type_id, quantity, status, payment_method, order_date)
              VALUES (?, ?, ?, ?, 'Completed', ?, CURDATE())");  // Changed status to Completed
          
          $stmt->execute([
              $_POST['client_name'],
              $_POST['contact'],
              $_POST['block_type'],
              $_POST['quantity'],
              $_POST['payment_method']  // Now matches SQL parameters
          ]);
          
          $orderId = $conn->lastInsertId();
          $success = "Order #$orderId created successfully! Payment recorded.";
      }

      // Add Collection
      if (isset($_POST['add_collection'])) {
          $stmt = $conn->prepare("INSERT INTO client_collections 
              (order_id, date, blocks_collected)
              VALUES (?, CURDATE(), ?)");  // Use current date automatically
          
          $stmt->execute([
              $_POST['order_id'],
              $_POST['collected_qty']
          ]);
          $success = "Collection recorded successfully!";
      }
  } catch (PDOException $e) {
      $error = "Error: " . $e->getMessage();
  }
}
// Get Data
$orders = $conn->query("
    SELECT co.*, bt.type_name, 
    COALESCE(SUM(cc.blocks_collected), 0) AS total_collected
    FROM clients_orders co
    LEFT JOIN block_types bt ON co.block_type_id = bt.id
    LEFT JOIN client_collections cc ON co.id = cc.order_id
    GROUP BY co.id
    ORDER BY co.order_date DESC
")->fetchAll();

$block_types = $conn->query("SELECT * FROM block_types")->fetchAll();
?>

<div class="container-fluid">
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Client Orders</h4>
            <div>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#orderModal">
                    <i class="fas fa-plus me-2"></i>Create Order
                </button>
            </div>
        </div>

        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 30px"></th>
                        <th>Order ID</th>
                        <th>Client</th>
                        <th>Block Type</th>
                        <th>Quantity</th>
                        <th>Collected</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): 
                        $remaining = $order['quantity'] - $order['total_collected'];
                    ?>
                    <tr>
                        <td>
                            <button class="btn btn-sm btn-link text-dark" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collections-<?= $order['id'] ?>">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </td>
                        <td>#<?= $order['id'] ?></td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($order['client_name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($order['contact']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($order['type_name']) ?></td>
                        <td><?= number_format($order['quantity']) ?></td>
                        <td><?= number_format($order['total_collected']) ?></td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" 
                                    style="width: <?= ($order['total_collected'] / $order['quantity'] * 100) ?>%">
                                    <?= round($order['total_collected'] / $order['quantity'] * 100) ?>%
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-<?= $order['status'] === 'Completed' ? 'success' : 'warning' ?>">
                                <?= $order['status'] ?>
                            </span>
                        </td>
                        <td><?= date('M j, Y', strtotime($order['order_date'])) ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#collectionModal"
                                    data-order-id="<?= $order['id'] ?>">
                                <i class="fas fa-box-open me-2"></i>Add Collection
                            </button>
                        </td>
                    </tr>
                    
                    <!-- Collection History Toggle -->
                    <tr class="collapse bg-light" id="collections-<?= $order['id'] ?>">
                        <td colspan="10">
                            <div class="ps-5 pe-4 py-3">
                                <h6 class="mb-3"><i class="fas fa-history me-2"></i>Collection History</h6>
                                <div class="row g-3">
                                    <?php 
                                    $collections = $conn->query("SELECT * FROM client_collections 
                                        WHERE order_id = {$order['id']} 
                                        ORDER BY date DESC")->fetchAll();
                                    
                                    foreach ($collections as $collection): ?>
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between">
                                                    <div>
                                                        <div class="fw-bold"><?= number_format($collection['blocks_collected']) ?></div>
                                                        <small class="text-muted">
                                                            <?= date('M j, Y', strtotime($collection['date'])) ?>
                                                        </small>
                                                    </div>
                                                    <div class="text-success">
                                                        <i class="fas fa-check-circle fa-2x"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if(empty($collections)): ?>
                                    <div class="col-12">
                                        <div class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p class="mb-0">No collections recorded yet</p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
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

<!-- Create Order Modal -->
<div class="modal fade" id="orderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-alt me-2"></i>New Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Client Name</label>
                        <input type="text" class="form-control" name="client_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" name="contact" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Block Type</label>
                        <select class="form-select" name="block_type" required>
                            <?php foreach ($block_types as $type): ?>
                            <option value="<?= $type['id'] ?>"><?= $type['type_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" min="1" required>
                    </div>
<div class="mb-3">
    <label class="form-label">Payment Method</label>
    <select class="form-select" name="payment_method" required>
        <option value="Cash">Cash</option>
        <option value="Mobile Money">Mobile Money</option>
    </select>
</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_order" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Collection Modal -->
<div class="modal fade" id="collectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="order_id" id="collectionOrderId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-box-open me-2"></i>Record Collection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Collection Date</label>
                        <input type="date" class="form-control" name="date" 
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity Collected</label>
                        <input type="number" class="form-control" 
                               name="collected_qty" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_collection" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Collection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Collection Modal Handler
    const collectionModal = document.getElementById('collectionModal');
    collectionModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        document.getElementById('collectionOrderId').value = button.dataset.orderId;
    });

    // Toggle Chevron Rotation
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
        button.addEventListener('click', function() {
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-rotate-180');
        });
    });
});
</script>