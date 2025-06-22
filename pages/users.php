<?php 
//require_once(".\config.php");
// pages/users.php
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])){
    try {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];

        $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':role' => $role
        ]);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
<h2 class="mb-4">User Management</h2>
<div class="card mb-4">
  <div class="card-header">
    <h4 class="mb-0">Users List</h4>
  </div>
  <div class="card-body">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Role</th>
        </tr>
      </thead>
      <tbody>
      <?php
      try {
          $users = $conn->query("SELECT id, username, role FROM users");
          while ($user = $users->fetch(PDO::FETCH_ASSOC)):
      ?>
        <tr>
          <td><?= htmlspecialchars($user['id']) ?></td>
          <td><?= htmlspecialchars($user['username']) ?></td>
          <td><?= htmlspecialchars($user['role']) ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Add User Form -->
<div class="card">
  <div class="card-header">
    <h4 class="mb-0">Add User</h4>
  </div>
  <div class="card-body">
    <form action="index.php?page=users" method="POST">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input type="text" class="form-control" name="username" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Role</label>
        <select class="form-select" name="role" required>
          <option value="admin">Admin</option>
          <option value="accounts">Accounts</option>
          <option value="it">IT</option>
          <option value="manager">Manager</option>
          <option value="worker">Worker</option>
        </select>
      </div>
      <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
    </form>
  </div>
</div>
<?php
      } catch (PDOException $e) {
          echo "Error: " . $e->getMessage();
      }
      ?>