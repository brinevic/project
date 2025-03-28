<?php
$conn = new mysqli("localhost", "root", "", "computer_booking_system");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Release expired computers
$conn->query("
    UPDATE computers 
    SET in_use = 0 
    WHERE id IN (SELECT computer_id FROM bookings WHERE end_time <= NOW())
");

// ✅ Delete expired bookings
$conn->query("DELETE FROM bookings WHERE end_time <= NOW()");

echo "success"; // ✅ Signal to JavaScript that update is complete
?>
