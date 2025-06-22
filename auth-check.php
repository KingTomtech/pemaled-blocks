<?php
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Optional: Role-based check
$allowedRoles = ['admin', 'manager', 'accounts', 'worker'];
if(isset($allowedRoles) && !in_array($_SESSION['role'], $allowedRoles)) {
    header("Location: unauthorized.php");
    exit();
}
?>