<?php
require_once '../auth-check.php';
require_once '../vendor/autoload.php'; // Include Composer dependencies

$allowedRoles = ['admin', 'manager', 'accounts'];
if(!in_array($_SESSION['role'], $allowedRoles)) {
    die("Unauthorized access");
}

$type = $_GET['type'] ?? 'production';
$format = $_GET['format'] ?? 'excel';

// Common data fetching
$startDate = $_GET['start'] ?? date('Y-m-01');
$endDate = $_GET['end'] ?? date('Y-m-t');

switch($type) {
    case 'production':
        $stmt = $conn->prepare("
            SELECT date, blocks_produced, waste_blocks, production_cost 
            FROM daily_production
            WHERE date BETWEEN ? AND ?
        ");
        $filename = "production-report";
        break;
    
    case 'maintenance':
        $stmt = $conn->prepare("
            SELECT m.date, e.name, m.description, m.cost 
            FROM equipment_maintenance m
            JOIN equipment e ON m.equipment_id = e.id
            WHERE m.date BETWEEN ? AND ?
        ");
        $filename = "maintenance-report";
        break;
    
    default:
        die("Invalid report type");
}

$stmt->execute([$startDate, $endDate]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export logic
if($format === 'excel') {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Add headers
    $headers = array_keys($data[0]);
    $sheet->fromArray($headers, NULL, 'A1');
    
    // Add data
    $sheet->fromArray($data, NULL, 'A2');
    
    // Generate file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    
} elseif($format === 'pdf') {
    $pdf = new \TCPDF();
    $pdf->AddPage();
    
    // Add title
    $pdf->SetFont('helvetica', 'B', 16);
    $pdf->Cell(0, 10, strtoupper($filename), 0, 1);
    
    // Add table
    $pdf->SetFont('helvetica', '', 10);
    $html = '<table border="1"><tr>';
    
    // Headers
    foreach(array_keys($data[0]) as $header) {
        $html .= '<th>'.htmlspecialchars($header).'</th>';
    }
    $html .= '</tr>';
    
    // Rows
    foreach($data as $row) {
        $html .= '<tr>';
        foreach($row as $cell) {
            $html .= '<td>'.htmlspecialchars($cell).'</td>';
        }
        $html .= '</tr>';
    }
    
    $html .= '</table>';
    $pdf->writeHTML($html, true, false, false, false, '');
    
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment;filename="'.$filename.'.pdf"');
    $pdf->Output('php://output', 'D');
}

exit();