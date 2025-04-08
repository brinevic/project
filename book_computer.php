<?php
session_start();
require_once __DIR__ . '/tcpdf/tcpdf.php';
require 'vendor/autoload.php'; // PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = new mysqli("localhost", "root", "", "computer_booking_system");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Check if computer_id is provided
if (!isset($_POST['computer_id']) || empty($_POST['computer_id'])) {
    $_SESSION['error'] = "No computer selected for booking.";
    header("Location: dashboard.php");
    exit();
}

$computer_id = intval($_POST['computer_id']);

date_default_timezone_set('Africa/Nairobi');
$current_time = date("H:i:s");
$current_date = date("Y-m-d");

// ✅ Prevent multiple bookings per day
$check_booking = $conn->query("SELECT * FROM bookings WHERE user_id = $user_id AND DATE(start_time) = '$current_date'");
if ($check_booking->num_rows > 0) {
    $_SESSION['error'] = "You have already booked a computer today. Please try again tomorrow.";
    header("Location: dashboard.php");
    exit();
}

// ✅ Determine booking time
if ($current_time >= "17:00:00") {
    $start_time = date("Y-m-d 07:00:00", strtotime("+1 day"));
} else {
    $start_time = date("Y-m-d H:i:s");
}

$end_time = date("Y-m-d H:i:s", strtotime($start_time . ' +2 hours'));
$ticket_number = "TKT-" . strtoupper(substr(md5(uniqid()), 0, 6));

// ✅ Fetch user info
$user_query = $conn->query("SELECT name, email FROM users WHERE id = $user_id");
$user = $user_query->fetch_assoc();
$user_name = $user['name'];
$user_email = $user['email'];

// ✅ Insert booking
$stmt = $conn->prepare("INSERT INTO bookings (user_id, computer_id, start_time, end_time, ticket_number) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("iisss", $user_id, $computer_id, $start_time, $end_time, $ticket_number);

if ($stmt->execute()) {
    // ✅ Update computer status
    $update_stmt = $conn->prepare("UPDATE computers SET in_use = 1 WHERE id = ?");
    $update_stmt->bind_param("i", $computer_id);
    $update_stmt->execute();

    // ✅ Get computer name
    $result = $conn->query("SELECT computer_name FROM computers WHERE id = $computer_id");
    $computer = $result->fetch_assoc();
    $computer_name = $computer['computer_name'];

    // ✅ Store in session
    $_SESSION['ticket'] = [
        'ticket_number' => $ticket_number,
        'computer_name' => $computer_name,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'user_name' => $user_name,
        'user_email' => $user_email
    ];

    // ✅ Generate PDF
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
    $receipt_path = __DIR__ . "/receipts/$file_name";
    $pdf->Output($receipt_path, 'F');

    $_SESSION['receipt'] = "receipts/$file_name";

    // ✅ Send Email with Attachment
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // or your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'brianvictor343@gmail.com'; // replace
        $mail->Password = 'yvbyyglifwylrfjv'; // replace with App Password (not your Gmail password)
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('brianvictor343@gmail.com', 'Library Computer Booking System');
        $mail->addAddress($user_email, $user_name);

        $mail->Subject = 'Your Library Computer Booking Receipt';
        $mail->Body = "Hi $user_name,\n\nAttached is your receipt for the computer booking:\nComputer: $computer_name\nStart: $start_time\nEnd: $end_time\n\nThank you for booking with us!";
        $mail->addAttachment($receipt_path);

        $mail->send();
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
    }

    header("Location: receipt.php");
    exit();
} else {
    echo "Booking Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
