<?php
include 'config.php'; // Ensure this contains PDO connection
include 'includes/header.php';
include 'includes/sidebar.php';
include 'includes/modals.php';

$page = $_GET['page'] ?? 'dashboard';

// Mapping allowed pages to their file paths
$pages = [
    'dashboard'          => 'pages/dashboard.php',
    'orders'             => 'pages/orders.php',
    'materials'          => 'pages/materials.php',
    'staff'              => 'pages/staff.php',
    'transactions'       => 'pages/transactions.php',
    'users'              => 'pages/users.php',
    'production_report'  => 'pages/production_report.php',
    'daily_production'  => 'pages/daily-production.php',
    'payroll'            => 'pages/payroll.php',
    'attendance'         => 'pages/attendance.php',
    'collections'        => 'pages/client-collections.php',
    'schedule'           => 'pages/schedule.php',
    'maintanance'        => 'pages/maintanance.php',
    'targets'            =>  'pages/targets.php',
    'logout'             => 'logout.php'
    
];

if (array_key_exists($page, $pages)) {
    include($pages[$page]);
} else {
    echo "<div class='card'><div class='card-body'>Page not found.</div></div>";
}

include 'includes/footer.php';
?>
