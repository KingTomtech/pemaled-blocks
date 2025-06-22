<?php
// pages/materials.php
include('../config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_material'])) {
    try {
        // Retrieve and sanitize form data
        $material_name = $_POST['material_name'];
        $quantity_remaining = (float) $_POST['quantity_remaining'];
        $unit = $_POST['unit'];
        $last_updated = $_POST['last_updated'];

        // Prepare and execute insert statement
        $sql = "INSERT INTO materials (material_name, quantity_remaining, unit, last_updated) 
                VALUES (:material_name, :quantity_remaining, :unit, :last_updated)";
        $stmt = $conn->prepare($sql);
        $success = $stmt->execute([
            ':material_name' => $material_name,
            ':quantity_remaining' => $quantity_remaining,
            ':unit' => $unit,
            ':last_updated' => $last_updated
        ]);

        if ($success) {
            // Redirect after successful insertion
            header("Location: ../index.php?page=materials");
            exit;
        } else {
            $errorInfo = $stmt->errorInfo();
            echo "Execution Error: " . $errorInfo[2];
        }
    } catch (PDOException $e) {
        echo "Database Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request";
}
?>
