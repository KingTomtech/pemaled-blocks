<?php
/*require_once 'header.php';
require_once 'auth_check.php'; // Ensures user is logged in
*/
// Restrict to managers/admins
if(!in_array($_SESSION['role'], ['admin', 'manager'])) {
    header("Location: dashboard.php");
    exit();
}

$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

if(isset($_POST['save_attendance'])) {
    try {
        $conn->beginTransaction();
        
        // Clear existing attendance for the date
        $conn->prepare("DELETE FROM staff_daily_log WHERE date = ?")
            ->execute([$date]);

        // Insert new attendance records
        if(isset($_POST['attendance'])) {
            $stmt = $conn->prepare("INSERT INTO staff_daily_log (staff_id, date) VALUES (?, ?)");
            foreach($_POST['attendance'] as $staffId) {
                $stmt->execute([$staffId, $date]);
            }
        }
        
        $conn->commit();
        header("Location: index.php?pages=attendance.php");
        exit();
    } catch(PDOException $e) {
        $conn->rollBack();
        $error = "Error saving attendance: " . $e->getMessage();
    }
}

// Get staff list
$staff = $conn->query("SELECT * FROM staff ")->fetchAll();

// Get present staff for selected date
$present = $conn->prepare("SELECT staff_id FROM staff_daily_log WHERE date = ?");
$present->execute([$date]);
$presentIds = $present->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Daily Attendance</h4>
            <form method="get" class="row g-2">
                <div class="col-auto">
                    <input type="date" name="date" value="<?= $date ?>" class="form-control">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Load</button>
                </div>
            </form>
        </div>
        
        <?php if(isset($error)): ?>
            <div class="alert alert-danger m-3"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success m-3">Attendance saved successfully!</div>
        <?php endif; ?>

        <form method="post">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Present</th>
                            <th>Staff Member</th>
                            <th>Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($staff as $worker): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="attendance[]" 
                                    value="<?= $worker['id'] ?>" 
                                    <?= in_array($worker['id'], $presentIds) ? 'checked' : '' ?>
                                    class="form-check-input">
                            </td>
                            <td><?= htmlspecialchars($worker['name']) ?></td>
                            <td><?= htmlspecialchars($worker['contact']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="save_attendance" class="btn btn-success">
                    Save Attendance
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'footer.php'; ?>