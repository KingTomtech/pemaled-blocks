<?php
// pages/create_order.php
include('../config.php');

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    // Retrieve POST values. No manual escaping is needed when using bound parameters.
    $client_name = $_POST['client_name'];
    $contact = $_POST['contact'];
    $block_type_id = (int) $_POST['block_type_id'];
    $quantity = (int) $_POST['quantity'];
    $order_date = $_POST['order_date'];

    // Get unit price for the selected block type using a prepared statement
    $stmt = $conn->prepare("SELECT unit_price FROM block_types WHERE id = ?");
    $stmt->execute([$block_type_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if($row){
        $unit_price = $row['unit_price'];
    } else {
        die("Invalid block type");
    }

    $status = 'Pending';

    // Insert the new order using a prepared statement
    $sql = "INSERT INTO clients_orders 
            (client_name, contact, block_type_id, quantity, unit_price, order_date, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if($stmt->execute([$client_name, $contact, $block_type_id, $quantity, $unit_price, $order_date, $status])){
        header("Location: ../index.php?page=dashboard");
        exit;
    } else {
        // Display error details if the query fails
        $errorInfo = $stmt->errorInfo();
        echo "Error: " . $errorInfo[2];
    }
} else {
    echo "Invalid Request";
}
?>
