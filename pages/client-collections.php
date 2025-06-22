<?php
//require_once '../header.php';
require_once 'config.php';
//include('E:\raver\htdocs\blocks-zinggati\auth-check.php');
//$_SESSION['role'] = 'admin';

// Restrict to managers/admins
/*if(!in_array($_SESSION['role'], ['admin', 'manager'])) {
    header("Location: dashboard.php");
    exit();
}*/

if(isset($_POST['record_collection'])) {
    $orderId = $_POST['order_id'];
    $blocks = (int)$_POST['blocks_collected'];
    $date = $_POST['collection_date'];

    try {
        $conn->beginTransaction();
        
        // Insert collection record
        $stmt = $conn->prepare("INSERT INTO client_collections (order_id, date, blocks_collected) 
                              VALUES (?, ?, ?)");
        $stmt->execute([$orderId, $date, $blocks]);

        // Get total collected
$stmt = $conn->prepare("
SELECT SUM(blocks_collected) 
FROM client_collections 
WHERE order_id = ?
");
$stmt->execute([$orderId]);
$totalCollected = $stmt->fetchColumn();

// Get order total
$stmt = $conn->prepare("
SELECT quantity 
FROM clients_orders 
WHERE id = ?
");
$stmt->execute([$orderId]);
$orderTotal = $stmt->fetchColumn();

        
        if($totalCollected >= $orderTotal) {
            $conn->prepare("UPDATE clients_orders SET status = 'Completed' WHERE id = ?")
                ->execute([$orderId]);
        }
        
        $conn->commit();
        header("Location: index.php?pages=collections");
        exit();
    } catch(PDOException $e) {
        $conn->rollBack();
        $error = "Error recording collection: " . $e->getMessage();
    }
}

// Get pending orders
$orders = $conn->query("
    SELECT o.*, bt.type_name, 
           (o.quantity - IFNULL(SUM(c.blocks_collected), 0)) AS remaining
    FROM clients_orders o
    LEFT JOIN client_collections c ON o.id = c.order_id
    JOIN block_types bt ON o.block_type_id = bt.id
    WHERE o.status = 'Pending'
    GROUP BY o.id
    HAVING remaining > 0
")->fetchAll();
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h4>Record Client Collections</h4>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger m-3"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success m-3">Collection recorded successfully!</div>
        <?php endif; ?>

        <div class="card-body">
            <form method="post">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Select Order</label>
                        <select name="order_id" class="form-select" required>
                            <?php foreach($orders as $order): ?>
                            <option value="<?= $order['id'] ?>">
                                <?= htmlspecialchars($order['client_name']) ?> - 
                                <?= $order['type_name'] ?> (Remaining: <?= $order['remaining'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Blocks Collected</label>
                        <input type="number" name="blocks_collected" 
                               class="form-control" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Collection Date</label>
                        <input type="date" name="collection_date" 
                               value="<?= date('Y-m-d') ?>" class="form-control" required>
                    </div>
                </div>
                <button type="submit" name="record_collection" class="btn btn-primary">
                    Record Collection
                </button>
            </form>
        </div>
    </div>
</div>
