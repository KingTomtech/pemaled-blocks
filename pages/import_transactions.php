<?php
require_once '../config.php'; // Ensure this path correctly includes your DB config
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate connection exists
        if (!$conn instanceof PDO) {
            throw new Exception("Database connection failed");
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Invalid file upload");
        }

        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, 'r');
        
        // Verify CSV read
        if ($handle === false) {
            throw new Exception("Failed to open CSV file");
        }

        // Skip header
        fgetcsv($handle);
        
        $conn->beginTransaction();
        $stmt = $conn->prepare("INSERT INTO transactions 
            (date, description, category, type, amount, method, notes) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");

        while (($data = fgetcsv($handle)) !== false) {
            // Validate CSV column count
            
            $stmt->execute([
                $data[1],  // date
                $data[2],  // description
                $data[3],  // category
                $data[4],  // type
                (float) str_replace(['zmk', ','], '', $data[5]),  // amount
                $data[6],  // method
                $data[7]   // notes
            ]);
        
        }

        $conn->commit();
        $_SESSION['message'] = "Successfully imported transactions!";
        
    } catch (Exception $e) {
        if ($conn instanceof PDO && $conn->inTransaction()) {
            $conn->rollBack();
        }
        $_SESSION['error'] = 'Import failed: ' . $e->getMessage();
    }
}

header('Location: ../index.php?page=transactions'); // Verify correct redirect path
exit;