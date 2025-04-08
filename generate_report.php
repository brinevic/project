<?php
require('tcpdf/tcpdf.php'); // Make sure TCPDF is installed and path is correct

$conn = new mysqli("localhost", "root", "", "computer_booking_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Get report type from URL
$report_type = isset($_GET['type']) ? $_GET['type'] : 'daily';

switch ($report_type) {
    case 'daily':
        $title = "Daily Bookings Report - " . date('Y-m-d');
        $filename = "daily_report_" . date('Y-m-d') . ".pdf";
        $date_condition = "DATE(b.start_time) = CURDATE()";
        break;

    case 'weekly':
        $title = "Weekly Bookings Report - Week " . date('W, Y');
        $filename = "weekly_report_" . date('Y_W') . ".pdf";
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $week_end = date('Y-m-d', strtotime('sunday this week'));
        $date_condition = "DATE(b.start_time) BETWEEN '$week_start' AND '$week_end'";
        break;

    case 'monthly':
        $title = "Monthly Bookings Report - " . date('F Y');
        $filename = "monthly_report_" . date('Y_m') . ".pdf";
        $month_start = date('Y-m-01');
        $month_end = date('Y-m-t');
        $date_condition = "DATE(b.start_time) BETWEEN '$month_start' AND '$month_end'";
        break;

    default:
        die("Invalid report type.");
}

// ✅ Fetch Booking Data
$sql = "
    SELECT u.name, u.email, c.computer_name, b.start_time, b.end_time 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN computers c ON b.computer_id = c.id
    WHERE $date_condition
    ORDER BY b.start_time ASC
";

$result = $conn->query($sql);

// ✅ Create PDF
$pdf = new TCPDF();
$pdf->SetCreator('Library Booking System');
$pdf->SetAuthor('Library Admin');
$pdf->SetTitle($title);
$pdf->SetMargins(5, 10, 5);
$pdf->AddPage();

// ✅ Report Header
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, $title, 0, 1, 'C');
$pdf->Ln(5);

// ✅ Table Headers
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(35, 8, 'Name', 1);
$pdf->Cell(55, 8, 'Email', 1);
$pdf->Cell(35, 8, 'Computer', 1);
$pdf->Cell(32, 8, 'Start Time', 1);
$pdf->Cell(32, 8, 'End Time', 1);
$pdf->Ln();

// ✅ Table Rows
$pdf->SetFont('helvetica', '', 9);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(35, 8, $row['name'], 1);
        $pdf->Cell(55, 8, $row['email'], 1);
        $pdf->Cell(35, 8, $row['computer_name'], 1);
        $pdf->Cell(32, 8, $row['start_time'], 1);
        $pdf->Cell(32, 8, $row['end_time'], 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 8, 'No bookings found for this period.', 1, 1, 'C');
}

// ✅ Output PDF
$pdf->Output($filename, 'D');
?>
