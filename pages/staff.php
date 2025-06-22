<?php
// pages/staff.php
require_once("config.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_staff'])) {
    // Retrieve the POST data directly (no manual escaping is needed with prepared statements)
    $name           = $_POST['name'];
    $role           = $_POST['role'];
    $contact        = $_POST['contact'];
    $salary         = (float) $_POST['salary'];
    $payment_status = $_POST['payment_status'];
    $last_payment   = $_POST['last_payment'];

    // Use a prepared statement to insert staff data
    $stmt = $conn->prepare("INSERT INTO staff (name, role, contact, salary, payment_status, last_payment) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt->execute([$name, $role, $contact, $salary, $payment_status, $last_payment])) {
        $errorInfo = $stmt->errorInfo();
        echo "Error: " . $errorInfo[2];
    }
}
?>

<h2 class="mb-4">Staff Management</h2>
<div class="card mb-4">
  <div class="card-header">
    <h4 class="mb-0">Staff List</h4>
  </div>
  <div class="card-body">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Role</th>
          <th>Contact</th>
          <th>Salary</th>
          <th>Payment Status</th>
          <th>Last Payment</th>
        </tr>
      </thead>
      <tbody>
      <?php
      // Retrieve staff records using PDO
      $staff_stmt = $conn->query("SELECT * FROM staff");
      while ($member = $staff_stmt->fetch(PDO::FETCH_ASSOC)):
      ?>
        <tr>
          <td><?php echo $member['id']; ?></td>
          <td><?php echo $member['name']; ?></td>
          <td><?php echo $member['role']; ?></td>
          <td><?php echo $member['contact']; ?></td>
          <td>zmk <?php echo number_format($member['salary'], 2); ?></td>
          <td><?php echo $member['payment_status']; ?></td>
          <td><?php echo $member['last_payment']; ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Staff Form -->
<div class="card">
  <div class="card-header">
    <h4 class="mb-0">Add Staff</h4>
  </div>
  <div class="card-body">
    <form action="index.php?page=staff" method="POST">
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" class="form-control" name="name" required>
      </div>
      <div class="mb-3">
  <label for="role" class="form-label">Role</label>
  <select id="role" name="role" class="form-select" required>
    <option value="" disabled selected>Select a role…</option>
    <option value="accounts">Accounts</option>
    <option value="it">IT</option>
    <option value="manager">Manager</option>
    <option value="staff">Staff</option>
    <!-- add more roles here as needed -->
  </select>
</div>

      <div class="mb-3">
        <label class="form-label">Contact</label>
        <input type="text" class="form-control" name="contact" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Salary</label>
        <input type="number" step="0.01" class="form-control" name="salary" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Payment Status</label>
        <select class="form-select" name="payment_status" required>
          <option value="Paid">Paid</option>
          <option value="Unpaid">Unpaid</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Last Payment</label>
        <input type="date" class="form-control" name="last_payment" required>
      </div>
      <button type="submit" name="add_staff" class="btn btn-primary">Add Staff</button>
    </form>
  </div>
</div>
