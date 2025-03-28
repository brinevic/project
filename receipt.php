<?php
session_start();
if (!isset($_SESSION['receipt'])) {
    echo "No receipt found.";
    exit();
}
$receipt_path = $_SESSION['receipt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Booking Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5 text-center">
        <h2>Booking Successful!</h2>
        <p>Your booking receipt is ready for download.</p>
        <a href="<?php echo $receipt_path; ?>" class="btn btn-primary" download>Download Receipt (PDF)</a>
        <br><br>
        <a href="dashboard.php" class="btn btn-secondary">Go to Dashboard</a>
    </div>
</body>
</html>
