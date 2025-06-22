<?php
// Verify worker access
if($_SESSION['role'] != 'worker') {
    header("Location: index.php?page=dashboard");
    exit();
}

$workerId = $_SESSION['user_id'];
$currentWeekStart = date('Y-m-d', strtotime('monday this week'));
$currentWeekEnd = date('Y-m-d', strtotime('sunday this week'));

// Get weekly schedule
$schedule = $conn->prepare("
    SELECT * FROM worker_schedules 
    WHERE staff_id = ? 
    AND schedule_date BETWEEN ? AND ?
    ORDER BY schedule_date, shift_start
");
$schedule->execute([$workerId, $currentWeekStart, $currentWeekEnd]);
$weeklySchedule = $schedule->fetchAll(PDO::FETCH_ASSOC);

// Get current tasks
$tasks = $conn->prepare("
    SELECT * FROM worker_schedules
    WHERE staff_id = ? 
    AND schedule_date >= CURDATE()
    AND status = 'scheduled'
    ORDER BY schedule_date
    LIMIT 5
");
$tasks->execute([$workerId]);
$currentTasks = $tasks->fetchAll(PDO::FETCH_ASSOC);

// Get attendance summary
$attendance = $conn->prepare("
    SELECT 
        COUNT(*) AS total_shifts,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed_shifts
    FROM worker_schedules
    WHERE staff_id = ?
    AND schedule_date BETWEEN ? AND ?
");
$attendance->execute([$workerId, $currentWeekStart, $currentWeekEnd]);
$attendanceSummary = $attendance->fetch(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h3 class="mb-4">My Work Schedule</h3>
    
    <!-- Current Week Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title">Week Summary</h5>
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1">Total Shifts</p>
                            <h2><?= $attendanceSummary['total_shifts'] ?></h2>
                        </div>
                        <div>
                            <p class="mb-1">Completed</p>
                            <h2><?= $attendanceSummary['completed_shifts'] ?></h2>
                        </div>
                    </div>
                    <p class="mb-0 text-muted small">
                        Week: <?= date('M j', strtotime($currentWeekStart)) ?> - <?= date('M j', strtotime($currentWeekEnd)) ?>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title mb-4">Current Tasks</h5>
                    <?php if(count($currentTasks) > 0): ?>
                        <div class="list-group">
                            <?php foreach($currentTasks as $task): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1"><?= htmlspecialchars($task['task_description']) ?></h6>
                                        <small class="text-muted">
                                            <?= date('D, M j', strtotime($task['schedule_date'])) ?> |
                                            <?= date('h:i A', strtotime($task['shift_start'])) ?> - <?= date('h:i A', strtotime($task['shift_end'])) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-primary">Scheduled</span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No upcoming tasks scheduled
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Schedule -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Weekly Schedule</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Shift Time</th>
                            <th>Task</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($weeklySchedule as $shift): ?>
                        <tr>
                            <td><?= date('M j, Y', strtotime($shift['schedule_date'])) ?></td>
                            <td><?= date('l', strtotime($shift['schedule_date'])) ?></td>
                            <td>
                                <?= date('h:i A', strtotime($shift['shift_start'])) ?> - 
                                <?= date('h:i A', strtotime($shift['shift_end'])) ?>
                            </td>
                            <td><?= htmlspecialchars($shift['task_description']) ?></td>
                            <td>
                                <span class="badge bg-<?= 
                                    ($shift['status'] == 'completed') ? 'success' : 
                                    (($shift['status'] == 'cancelled') ? 'danger' : 'primary') ?>">
                                    <?= ucfirst($shift['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if(count($weeklySchedule) === 0): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                No shifts scheduled for this week
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Shift Statistics -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5>Total Hours This Week</h5>
                    <?php
                    $totalHours = $conn->prepare("
                        SELECT SUM(TIMEDIFF(shift_end, shift_start)) AS total 
                        FROM worker_schedules 
                        WHERE staff_id = ? 
                        AND schedule_date BETWEEN ? AND ?
                    ");
                    $totalHours->execute([$workerId, $currentWeekStart, $currentWeekEnd]);
                    $hours = $totalHours->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <h2 class="text-success">
                        <?= $hours['total'] ? round(substr($hours['total'], 0, 2)) : 0 ?> hrs
                    </h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h5>Upcoming Shifts</h5>
                    <?php
                    $upcoming = $conn->prepare("
                        SELECT COUNT(*) AS total 
                        FROM worker_schedules 
                        WHERE staff_id = ? 
                        AND schedule_date > CURDATE()
                    ");
                    $upcoming->execute([$workerId]);
                    $upcomingShifts = $upcoming->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <h2 class="text-info"><?= $upcomingShifts['total'] ?></h2>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h5>Completion Rate</h5>
                    <?php
                    $completion = $conn->prepare("
                        SELECT 
                            (SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) / COUNT(*)) * 100 AS rate
                        FROM worker_schedules 
                        WHERE staff_id = ?
                    ");
                    $completion->execute([$workerId]);
                    $rate = $completion->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <h2 class="text-warning">
                        <?= number_format($rate['rate'] ?? 0, 1) ?>%
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>