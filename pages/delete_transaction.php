<?php
require_once '../config.php';
session_start();

if (!isset($_GET['id'])) {
    header('Location: ../index.php?page=transactions');
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $_SESSION['message'] = 'Transaction deleted successfully!';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Delete failed: ' . $e->getMessage();
}

header('Location: ../index.php?page=transactions');
exit;
?>