<?php 

session_start();
$conn = new mysqli("localhost", "root", "", "computer_booking_system");

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// ✅ Fetch reported defects
$defects = $conn->query("
    SELECT d.id, c.computer_name, u.name, d.defect_description, d.status, d.reported_at
    FROM computer_defects d
    JOIN computers c ON d.computer_id = c.id
    JOIN users u ON d.user_id = u.id
    ORDER BY d.reported_at DESC
");

// ✅ Mark defect as resolved
if (isset($_GET['resolve_id'])) {
    $id = $_GET['resolve_id'];
    $conn->query("UPDATE computer_defects SET status = 'Resolved' WHERE id = '$id'");
    echo "<script>alert('Defect marked as resolved!'); window.location.href='admin_defects.php';</script>";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Reported Defects</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- ✅ HEADER / NAVBAR -->
    <?php include 'admin_navbar.php'; ?>

    <main class="container mt-4">
        <h3 class="mt-4">Reported Computer Defects</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Computer Name</th>
                    <th>Reported By</th>
                    <th>Defect Description</th>
                    <th>Status</th>
                    <th>Reported At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $defects->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['computer_name']; ?></td>
                        <td><?php echo $row['username']; ?></td>
                        <td><?php echo $row['defect_description']; ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo $row['reported_at']; ?></td>
                        <td>
                            <?php if ($row['status'] === 'Pending') { ?>
                                <a href="?resolve_id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm">Mark as Resolved</a>
                            <?php } else { ?>
                                <span class="badge bg-success">Resolved</span>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </main>

    <!-- ✅ FOOTER -->
    <?php include 'footer.php'; ?>
</body>
</html>
