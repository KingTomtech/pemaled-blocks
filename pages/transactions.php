<?php
// Start session and database connection
require_once './config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    try {
        $stmt = $conn->prepare("
            INSERT INTO transactions 
            (type, date, description, amount, method, notes)
            VALUES
            (:type, :date, :description, :amount, :method, :notes)
        ");
        
        $stmt->execute([
            ':type' => $_POST['type'],
            ':date' => $_POST['date'],
            ':description' => $_POST['description'],
            ':amount' => $_POST['amount'],
            ':method' => $_POST['method'],
            ':notes' => $_POST['notes'] ?? null
        ]);
        
        $_SESSION['message'] = 'Transaction added successfully!';
        $_SESSION['message_type'] = 'success';
        header("Location: index.php?page=transactions");
        exit();
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Error adding transaction: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        header("Location: index.php?page=transactions");
        exit();
    }
}

// Transaction listing logic
$allowedTypes = ['Income', 'Expense'];
$typeFilter = '';
if (isset($_GET['type']) && in_array($_GET['type'], $allowedTypes, true)) {
    $typeFilter = $_GET['type'];
}

$sql = "
    SELECT t.*, COALESCE(o.id, 0) AS order_id
    FROM transactions t
    LEFT JOIN clients_orders o
      ON t.notes LIKE CONCAT('%Order ID: ', o.id, '%')
";
if ($typeFilter !== '') {
    $sql .= " WHERE t.type = :type";
}
$sql .= " ORDER BY t.date DESC";

$stmt = $conn->prepare($sql);
if ($typeFilter !== '') {
    $stmt->bindParam(':type', $typeFilter, PDO::PARAM_STR);
}
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- HTML Output -->
<h2 class="mb-4">Transactions</h2>
<div class="card mb-4">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="mb-0">Financial Records</h4>
    <div>
      <!-- Add Transaction Button -->
      <button class="btn btn-sm btn-success me-2" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
        <i class="fas fa-plus"></i> Add Transaction
      </button>
      
      <!-- Filter Buttons -->
      <a href="index.php?page=transactions&type=Income"
         class="btn btn-sm <?= $typeFilter === 'Income' ? 'btn-primary' : 'btn-outline-primary' ?>">
        Income
      </a>
      <a href="index.php?page=transactions&type=Expense"
         class="btn btn-sm <?= $typeFilter === 'Expense' ? 'btn-danger' : 'btn-outline-danger' ?>">
        Expenses
      </a>
      <a href="index.php?page=transactions"
         class="btn btn-sm btn-outline-secondary">
        Reset
      </a>
    </div>
  </div>

  <!-- Messages -->
  <div class="card-body">
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message_type'] ?> alert-dismissible fade show" role="alert">
      <?= $_SESSION['message'] ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php 
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    endif; ?>

    <table class="table table-hover">
      <!-- Table content same as before -->
      <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Description</th>
          <th>Amount (ZMW)</th>
          <th>Payment Method</th>
          <th>Related Order</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($transactions as $tran): 
            $isOrder = $tran['order_id'] > 0;
            $amount  = number_format($tran['amount'], 2);
        ?>
        <tr>
          <td><?= htmlspecialchars($tran['date'], ENT_QUOTES) ?></td>
          <td>
            <?= htmlspecialchars($tran['description'], ENT_QUOTES) ?>
            <?php if (!empty($tran['notes'])): ?>
              <div class="text-muted small mt-1">
                <?= htmlspecialchars($tran['notes'], ENT_QUOTES) ?>
              </div>
            <?php endif; ?>
          </td>
          <td class="fw-bold">ZMW <?= $amount ?></td>
          <td>
            <span class="badge bg-<?= $tran['method'] === 'Cash' ? 'success' : 'info' ?>">
              <?= htmlspecialchars($tran['method'], ENT_QUOTES) ?>
            </span>
          </td>
          <td>
            <?php if ($isOrder): ?>
              <a href="index.php?page=orders"
                 class="btn btn-sm btn-outline-primary">
                View Order #<?= (int)$tran['order_id'] ?>
              </a>
            <?php endif; ?>
          </td>
          <td>
            <a href="pages/edit_transaction.php?id=<?= (int)$tran['id'] ?>"
               class="btn btn-sm btn-warning">
              <i class="fas fa-edit"></i> Edit
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<div class="mb-3">
  <a href="pages/export_transactions.php" class="btn btn-success">Export to CSV</a>
  <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#importModal">
    Import from CSV
  </button>
</div>
<!-- Add Transaction Modal -->
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-labelledby="addTransactionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title" id="addTransactionModalLabel">New Transaction</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Type</label>
            <select class="form-select" name="type" required>
              <option value="">Select Type</option>
              <option value="Income">Income</option>
              <option value="Expense">Expense</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" class="form-control" name="date" required 
                   value="<?= date('Y-m-d') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" class="form-control" name="description" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Amount (ZMW)</label>
            <input type="number" class="form-control" name="amount" 
                   step="0.01" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Payment Method</label>
            <select class="form-select" name="method" required>
              <option value="Cash">Cash</option>
              <option value="Bank Transfer">Bank Transfer</option>
              <option value="Mobile Money">Mobile Money</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Notes</label>
            <textarea class="form-control" name="notes" rows="3"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" name="add_transaction" class="btn btn-primary">Save Transaction</button>
        </div>
      </form>
    </div>
  </div>
</div>