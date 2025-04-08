<?php 
session_start();
$conn = new mysqli("localhost", "root", "", "computer_booking_system");

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// ✅ Ensure expired computers are updated before fetching
$conn->query("
    UPDATE computers 
    SET in_use = 0 
    WHERE id IN (SELECT computer_id FROM bookings WHERE end_time <= NOW())
");

// ✅ Remove expired bookings
$conn->query("DELETE FROM bookings WHERE end_time <= NOW()");

// ✅ Fetch Available Computers
$availableComputers = $conn->query("SELECT * FROM computers WHERE in_use = 0");

// ✅ Fetch Active Bookings
$bookedComputers = $conn->query("
    SELECT b.id, c.computer_name, b.start_time, b.end_time,
           UNIX_TIMESTAMP(b.end_time) - UNIX_TIMESTAMP(NOW()) AS time_until_end
    FROM bookings b
    JOIN computers c ON b.computer_id = c.id
    WHERE NOW() < b.end_time
");
if (isset($_SESSION['error'])): ?>
    <div id="error-alert" class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <script>
        setTimeout(() => {
            const alertBox = document.getElementById("error-alert");
            if (alertBox) {
                alertBox.classList.remove("show");
                alertBox.classList.add("fade");
                setTimeout(() => alertBox.remove(), 500); // fully remove from DOM
            }
        }, 5000); // 5 seconds
    </script>
<?php endif; 

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Computer Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function startCountdown() {
            let timers = document.querySelectorAll(".timer");
            timers.forEach(timer => {
                let remainingTime = parseInt(timer.dataset.time);
                let bookingId = timer.dataset.bookingId;
                let row = timer.closest("tr");

                let interval = setInterval(() => {
                    if (remainingTime <= 0) {
                        clearInterval(interval);
                        timer.innerHTML = "Session Expired!";
                        row.remove(); // ✅ Remove expired booking row

                        // ✅ Refresh available computers
                        fetch("fetch_available_computers.php")
                            .then(response => response.text())
                            .then(data => {
                                document.getElementById("available-computers").innerHTML = data;
                            });

                    } else {
                        let hours = Math.floor(remainingTime / 3600);
                        let minutes = Math.floor((remainingTime % 3600) / 60);
                        let seconds = remainingTime % 60;
                        timer.innerHTML = `${hours}h ${minutes}m ${seconds}s`;
                        remainingTime--;
                    }
                }, 1000);
            });
        }

        window.onload = function() {
            startCountdown();
        };
    </script>
</head>
<body class="bg-light">
    <!-- ✅ HEADER / NAVBAR -->
    <?php include 'navbar.php'; ?>

    <main class="container mt-4">
    <div class="container text-center mt-5">
    <h2 class="fw-bold text-primary">Library Computer Booking System</h2>
</div>


        <h3 class="mt-4">Available Computers</h3>
        <div id="available-computers">
            <?php if ($availableComputers->num_rows > 0) { ?>
                <form method="POST" action="book_computer.php">
                    <select name="computer_id" class="form-control mb-2">
                        <?php while ($row = $availableComputers->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['computer_name']}</option>";
                        } ?>
                    </select>
                    <button type="submit" class="btn btn-success">Book computer</button>
                </form>
            <?php } else { ?>
                <div class="alert alert-info">No computers available. Try again later.</div>
            <?php } ?>
        </div>

        <h3 class="mt-4">Booked Computers</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Computer Name</th>
                    <th>Time Remaining</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $bookedComputers->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['computer_name']; ?></td>
                        <td class="timer" data-time="<?php echo max($row['time_until_end'], 0); ?>" data-booking-id="<?php echo $row['id']; ?>"></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>

    <!-- ✅ FOOTER -->
    <?php include 'footer.php'; ?>
</body>
</html>
