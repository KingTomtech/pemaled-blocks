<?php
require_once '../config.php';

try {
    // Validate database connection
    if (!$conn instanceof PDO) {
        throw new RuntimeException("Database connection failed");
    }

    // Prepare data before headers
    $stmt = $conn->query("SELECT id, date, description, category, type, amount, method, notes 
                        FROM transactions ORDER BY id DESC");
    if (!$stmt) {
        throw new RuntimeException("Failed to fetch transactions: " . implode(' ', $conn->errorInfo()));
    }

    // Store data in memory first to catch errors
    $outputBuffer = fopen('php://memory', 'w');
    
    // Add BOM for UTF-8 compatibility with Excel
    fwrite($outputBuffer, "\xEF\xBB\xBF");
    
    // Write headers
    fputcsv($outputBuffer, ['ID', 'Date', 'Description', 'Category', 'Type', 'Amount', 'Method', 'Notes']);

    // Format data rows
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Format amount with ZMK if needed (matches your display format)
        $row['amount'] = 'ZMK ' . number_format($row['amount'], 2);
        fputcsv($outputBuffer, $row);
    }

    // Now send headers and output
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=transactions_'.date('Ymd_His').'.csv');
    
    // Send buffered content
    rewind($outputBuffer);
    fpassthru($outputBuffer);
    fclose($outputBuffer);
    exit;

} catch (Exception $e) {
    // Handle errors before any output
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['error'] = 'Export failed: ' . $e->getMessage();
    header('Location: ../transactions.php');
    exit;
}
?>