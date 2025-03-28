<?php
$conn = new mysqli("localhost", "root", "", "computer_booking_system");

$availableComputers = $conn->query("SELECT * FROM computers WHERE in_use = 0");

if ($availableComputers->num_rows > 0) {
    echo '<form method="POST" action="book_computer.php">
            <select name="computer_id" class="form-control mb-2">';
    while ($row = $availableComputers->fetch_assoc()) {
        echo "<option value='{$row['id']}'>{$row['computer_name']}</option>";
    }
    echo '</select>
          <button type="submit" class="btn btn-success">Book</button>
          </form>';
} else {
    echo '<div class="alert alert-info">No computers available. Try again later.</div>';
}
?>
