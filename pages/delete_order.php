<?php
// pages/delete_order.php
include('../config.php');

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    // Prepare the DELETE statement
    $stmt = $conn->prepare("DELETE FROM clients_orders WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: ../index.php?page=dashboard");
exit;
?>
