<?php
require_once("../header.php");
require_once '../config.php';

if (!isset($_GET['id'])) {
    header('Location: ../index.php?page=transactions');
    exit;
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
$stmt->execute([$id]);
$transaction = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction) {
    header('Location: ../index.php?page=transactions');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare("UPDATE transactions SET 
            date = ?, 
            description = ?, 
            category = ?, 
            type = ?, 
            amount = ?, 
            method = ?, 
            notes = ? 
            WHERE id = ?");
        
        $stmt->execute([
            $_POST['date'],
            $_POST['description'],
            $_POST['category'],
            $_POST['type'],
            $_POST['amount'],
            $_POST['method'],
            $_POST['notes'],
            $id
        ]);
        
        $_SESSION['message'] = 'Transaction updated successfully!';
        header('Location: ../index.php?page=transactions');
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Update failed: ' . $e->getMessage();
    }
}
?>


<div class="container py-5">
    <h2 class="mb-4">Edit Transaction</h2>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($transaction['date']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="description" value="<?= htmlspecialchars($transaction['description']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <input type="text" class="form-control" name="category" value="<?= htmlspecialchars($transaction['category']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Type</label>
                    <select class="form-select" name="type" required>
                        <option value="income" <?= $transaction['type'] === 'income' ? 'selected' : '' ?>>Income</option>
                        <option value="expense" <?= $transaction['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Amount</label>
                    <input type="number" step="0.01" class="form-control" name="amount" value="<?= htmlspecialchars($transaction['amount']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Method</label>
                    <input type="text" class="form-control" name="method" value="<?= htmlspecialchars($transaction['method']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes</label>
                    <textarea class="form-control" name="notes"><?= htmlspecialchars($transaction['notes']) ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Update Transaction</button>
                <a href="../index.php?page=transactions" class="btn btn-secondary ms-2">Cancel</a>
            </form>
        </div>
    </div>
</div>

</body>
</html>
