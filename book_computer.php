<?php
session_start();
require_once __DIR__ . '/tcpdf/tcpdf.php'; // Ensure correct path

$conn = new mysqli("localhost", "root", "", "computer_booking_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$computer_id = $_POST['computer_id'];

date_default_timezone_set('Africa/Nairobi'); 

$current_time = date("H:i:s");
$current_date = date("Y-m-d");

// ✅ Prevent Multiple Bookings per Day
$check_booking = $conn->query("SELECT * FROM bookings WHERE user_id = $user_id AND DATE(start_time) = '$current_date'");
if ($check_booking->num_rows > 0) {
    echo "<script>alert('You have already booked a computer today! Please try again tomorrow.'); window.location.href='dashboard.php';</script>";
    exit();
}

// ✅ Set Booking Start Time
if ($current_time >= "17:00:00") {
    $start_time = date("Y-m-d 07:00:00", strtotime("+1 day"));
} else {
    $start_time = date("Y-m-d H:i:s");
}

$end_time = date("Y-m-d H:i:s", strtotime($start_time . ' +2 hours'));
$ticket_number = "TKT-" . strtoupper(substr(md5(uniqid()), 0, 6));

// ✅ Fetch user details
$user_query = $conn->query("SELECT name, email FROM users WHERE id = $user_id");
$user = $user_query->fetch_assoc();
$user_name = $user['name'];
$user_email = $user['email'];

// ✅ Insert booking details
$stmt = $conn->prepare("INSERT INTO bookings (user_id, computer_id, start_time, end_time, ticket_number) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("iisss", $user_id, $computer_id, $start_time, $end_time, $ticket_number);

if ($stmt->execute()) {
    $update_query = "UPDATE computers SET in_use = 1 WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $computer_id);
    $update_stmt->execute();

    $result = $conn->query("SELECT computer_name FROM computers WHERE id = $computer_id");
    if ($result->num_rows > 0) {
        $computer = $result->fetch_assoc();
        $computer_name = $computer['computer_name'];

        $_SESSION['ticket'] = [
            'ticket_number' => $ticket_number,
            'computer_name' => $computer_name,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'user_name' => $user_name,
            'user_email' => $user_email
        ];

        // ✅ Generate PDF Receipt
        $pdf = new TCPDF();
        $pdf->SetCreator('Library Booking System');
        $pdf->SetAuthor('Library');
        $pdf->SetTitle('Booking Receipt');
        $pdf->SetHeaderData('', 0, 'Library Computer Booking', "Booking Confirmation\nTicket: $ticket_number");

        $pdf->AddPage();
        $html = "
            <h2>Library Computer Booking Receipt</h2>
            <p><strong>Ticket Number:</strong> $ticket_number</p>
            <p><strong>User Name:</strong> $user_name</p>
            <p><strong>Email:</strong> $user_email</p>
            <p><strong>Computer Name:</strong> $computer_name</p>
            <p><strong>Start Time:</strong> $start_time</p>
            <p><strong>End Time:</strong> $end_time</p>
            <p>Thank you for using our service!</p>
        ";

        $pdf->writeHTML($html, true, false, true, false, '');

        $file_name = "receipt_$ticket_number.pdf";
        $pdf->Output(__DIR__ . "/receipts/$file_name", 'F'); // Save PDF file

        $_SESSION['receipt'] = "receipts/$file_name"; // Store receipt path
    }

    header("Location: receipt.php"); // Redirect to receipt page
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
