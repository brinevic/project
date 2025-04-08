<?php



session_start();
$conn = new mysqli("localhost", "root", "", "computer_booking_system");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle Adding a Computer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['computer_name'])) {
    $computer_name = trim($_POST['computer_name']);
    if (!empty($computer_name)) {
        $stmt = $conn->prepare("INSERT INTO computers (computer_name, status, in_use) VALUES (?, 'available', 0)");
        $stmt->bind_param("s", $computer_name);
        echo ($stmt->execute()) ? "success" : "error: " . $stmt->error;
        $stmt->close();
    } else {
        echo "error: Computer name cannot be empty";
    }
    exit();
}

// Handle Removing a Computer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM computers WHERE id = ?");
    $stmt->bind_param("i", $id);
    echo ($stmt->execute()) ? "success" : "error: " . $stmt->error;
    $stmt->close();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="bg-light">

    <!-- ✅ Header / Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="admin_logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center text-success">Admin Dashboard</h2>

        <!-- ✅ Add Computer Form -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Add a Computer</h5>
                <form id="addComputerForm">
                    <input type="text" name="computer_name" id="computer_name" placeholder="Computer Name" class="form-control mb-2" required>
                    <button type="submit" class="btn btn-primary w-100">Add Computer</button>
                </form>
                <div id="message" class="mt-2"></div>
            </div>
        </div>

        <!-- ✅ List of Computers -->
        <h4 class="mt-4">Available Computers</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Computer Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="computerTable">
                <?php
                $computers = $conn->query("SELECT * FROM computers");
                while ($row = $computers->fetch_assoc()) {
                    echo "<tr id='row-{$row['id']}'>
                        <td>{$row['id']}</td>
                        <td>{$row['computer_name']}</td>
                        <td><button class='btn btn-danger btn-sm delete-computer' data-id='{$row['id']}'>Remove</button></td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- ✅ Generate Reports -->
        <h4 class="mt-4">Generate Reports</h4>
        <a href="generate_report.php?type=daily" class="btn btn-info">Download Daily Report</a>
        <a href="generate_report.php?type=weekly" class="btn btn-primary">Download Weekly Report</a>
        <a href="generate_report.php?type=monthly" class="btn btn-success">Download Monthly Report</a>

        <a href="admin_logout.php" class="btn btn-danger mt-3">Logout</a>
    </div>

    <!-- ✅ Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p>&copy; <?php echo date("Y"); ?> Library Computer Booking System. All Rights Reserved.</p>
    </footer>

    <script>
        $(document).ready(function() {
            // ✅ Add Computer via AJAX
            $("#addComputerForm").submit(function(event) {
                event.preventDefault();
                var computer_name = $("#computer_name").val().trim();
                if (computer_name === "") {
                    $("#message").html("<div class='alert alert-danger'>Computer name cannot be empty!</div>");
                    return;
                }

                $.ajax({
                    url: "admin_dashboard.php",
                    type: "POST",
                    data: { computer_name: computer_name },
                    success: function(response) {
                        if (response === "success") {
                            location.reload();
                        } else {
                            $("#message").html("<div class='alert alert-danger'>" + response + "</div>");
                        }
                    }
                });
            });

            // ✅ Delete Computer via AJAX
            $(document).on("click", ".delete-computer", function() {
                var delete_id = $(this).data("id");

                if (confirm("Are you sure you want to delete this computer?")) {
                    $.ajax({
                        url: "admin_dashboard.php",
                        type: "POST",
                        data: { delete_id: delete_id },
                        success: function(response) {
                            if (response === "success") {
                                location.reload();
                            } else {
                                alert("Error: " + response);
                            }
                        }
                    });
                }
            });
        });
    </script>

</body>
</html>
