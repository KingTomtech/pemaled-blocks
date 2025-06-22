<?php
// pages/transactions.php
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_transaction'])) {
    try {
        // Retrieve POST values and cast where necessary.
        $date = $_POST['date'];
        $description = $_POST['description'];
        $category = $_POST['category'];
        $type = $_POST['type'];
        $amount = (float) $_POST['amount'];
        $method = $_POST['method'];
        $notes = $_POST['notes'];

        // Prepare the SQL statement using named parameters
        $sql = "INSERT INTO transactions (date, description, category, type, amount, method, notes)
                VALUES (:date, :description, :category, :type, :amount, :method, :notes)";
        $stmt = $conn->prepare($sql);
        
        // Execute the statement with an array of values to bind to the named parameters
        if ($stmt->execute([
            ':date' => $date,
            ':description' => $description,
            ':category' => $category,
            ':type' => $type,
            ':amount' => $amount,
            ':method' => $method,
            ':notes' => $notes
        ])) {
            // Redirect to the transactions dashboard or another appropriate page upon successful insert
            header("Location: ../index.php?page=transactions");
            exit;
        } else {
            // In case the query does not execute, get detailed error information
            $errorInfo = $stmt->errorInfo();
            echo "Execution Error: " . $errorInfo[2];
        }
    } catch (PDOException $e) {
        // Catch any PDO errors and display a user-friendly message.
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request";
}
?>
