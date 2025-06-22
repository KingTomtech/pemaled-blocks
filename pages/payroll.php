<?php //include('auth_check.php');// Ensure user is accounts/admin
//require_once("../config.php");
//require_once('../header.php'); // sidebar 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weekly Payroll</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Weekly Payroll</h2>
        
        <!-- Week Selection -->
        <form method="post" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <input type="week" name="week" class="form-control" 
                           value="<?= date('Y-\WW') ?>" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="load_payroll" class="btn btn-primary">
                        Load Payroll
                    </button>
                </div>
            </div>
        </form>

        <?php
        if(isset($_POST['load_payroll'])) {
            $week = $_POST['week'];
            list($year, $weekNum) = explode('-W', $week);
            
            // Get payroll data
            $stmt = $conn->prepare("
                SELECT s.id, s.name, COUNT(l.id) AS days_worked,
                       COUNT(l.id) * 35 AS total_pay
                FROM staff s
                LEFT JOIN staff_daily_log l 
                    ON s.id = l.staff_id 
                    AND YEARWEEK(l.date, 1) = ?
                WHERE s.role = 'worker'
                GROUP BY s.id
            ");
            $stmt->execute([$year . $weekNum]);
            $payroll = $stmt->fetchAll();
        ?>
        
        <!-- Payroll Table -->
        <form method="post">
            <input type="hidden" name="week" value="<?= $week ?>">
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Staff Name</th>
                        <th>Days Worked</th>
                        <th>Total Pay (K)</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($payroll as $worker): ?>
                    <tr>
                        <td><?= htmlspecialchars($worker['name']) ?></td>
                        <td><?= $worker['days_worked'] ?></td>
                        <td><?= number_format($worker['total_pay'], 2) ?></td>
                        <td>
                            <?php if($worker['days_worked'] > 0): ?>
                            <button type="submit" name="mark_paid[]" 
                                    value="<?= $worker['id'] ?>" 
                                    class="btn btn-sm btn-success">
                                Mark Paid
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
        <?php } ?>
    </div>
</body>
</html>

<?php
// Handle payment marking
if(isset($_POST['mark_paid'])) {
    $staffIds = $_POST['mark_paid'];
    $week = $_POST['week'];
    
    foreach($staffIds as $staffId) {
        // Update staff payment status
        $pdo->prepare("
            UPDATE staff SET 
                payment_status = 'Paid',
                last_payment = CURDATE()
            WHERE id = ?
        ")->execute([$staffId]);
        
        // Record transaction
        $stmt = $pdo->prepare("
            INSERT INTO transactions (date, description, type, amount, category)
            VALUES (CURDATE(), ?, 'Expense', ?, 'Salaries')
        ");
        
        // Get payment amount
        $payStmt = $pdo->prepare("
            SELECT COUNT(*) * 35 AS total 
            FROM staff_daily_log 
            WHERE staff_id = ? 
              AND YEARWEEK(date, 1) = ?
        ");
        list($year, $weekNum) = explode('-W', $week);
        $payStmt->execute([$staffId, $year . $weekNum]);
        $total = $payStmt->fetchColumn();
        
        $stmt->execute([
            "Salary Payment - " . $staffId,
            $total
        ]);
    }
    
    header("Location: payroll.php?success=1");
    exit();
}
?>