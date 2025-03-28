<?php
session_start();
$conn = new mysqli("localhost", "root", "", "computer_booking_system");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$success = "";
$error = "";

// User Registration
if (isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $country_code = $_POST['country_code'];
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } elseif (!preg_match('/^[0-9]{7,15}$/', $mobile)) {
        $error = "Invalid mobile number! Enter 7-15 digits only.";
    } else {
        $full_mobile = $country_code . $mobile;
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = $conn->prepare("SELECT * FROM users WHERE email=?");
        $sql->bind_param("s", $email);
        $sql->execute();
        $result = $sql->get_result();

        if ($result->num_rows > 0) {
            $error = "Email already exists!";
        } else {
            $sql = $conn->prepare("INSERT INTO users (name, email, mobile, password) VALUES (?, ?, ?, ?)");
            $sql->bind_param("ssss", $name, $email, $full_mobile, $hashed_password);

            if ($sql->execute()) {
                $success = "Registration successful! You can now log in.";
            } else {
                $error = "Error: " . $sql->error;
            }
        }
    }
}

// User Login
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = $conn->prepare("SELECT * FROM users WHERE email=?");
    $sql->bind_param("s", $email);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['role'] = $row['role'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid password!";
        }
    } else {
        $error = "Please register first!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login/Register - Computer Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #d3dafb, #ecddfa);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: Arial, sans-serif;
        }
        .container {
            width: 400px;
        }
        .card {
            padding: 20px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            background: #fff;
            text-align: center;
        }
        .btn-custom {
            background: #ff7eb3;
            border: none;
            transition: 0.3s;
            color: white;
            font-weight: bold;
        }
        .btn-custom:hover {
            background: #ff4d6d;
        }
        .form-control {
            border-radius: 20px;
            text-align: center;
        }
        .toggle-link {
            color: #007bff;
            cursor: pointer;
            text-align: center;
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }
        .password-container {
            position: relative;
        }
        .password-container input {
            width: 100%;
            padding-right: 40px;
        }
        .password-container .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #007bff;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center text-dark mb-4">Library Computer Booking System</h2>

    <!-- Alert Messages -->
    <?php if ($error): ?>
        <div class="alert alert-danger fade show" id="alertMessage"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success fade show" id="alertMessage"><?php echo $success; ?></div>
    <?php endif; ?>

    <!-- Registration Form -->
    <div id="registerForm" class="card">
        <h5 class="text-center text-primary">Register</h5>
        <form method="POST">
            <input type="text" name="name" class="form-control mb-2" placeholder="Full Name" required>
            <input type="email" name="email" class="form-control mb-2" placeholder="Email Address" required>

            <div class="row mb-2">
                <div class="col-4">
                    <select name="country_code" class="form-control">
                    <option value="+254">+254 (Kenya)</option>
                        <option value="+1">+1 (USA)</option>
                        <option value="+44">+44 (UK)</option>
                        <option value="+91">+91 (India)</option>
                        <option value="+254">+254 (Kenya)</option>
                    </select>
                </div>
                <div class="col-8">
                    <input type="text" name="mobile" class="form-control" placeholder="Mobile Number" required>
                </div>
            </div>

            <div class="password-container mb-2">
                <input type="password" id="regPassword" name="password" class="form-control" placeholder="Password" required>
                <span class="toggle-password" onclick="togglePassword('regPassword')">👁️</span>
            </div>

            <div class="password-container mb-2">
                <input type="password" id="confirmPassword" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                <span class="toggle-password" onclick="togglePassword('confirmPassword')">👁️</span>
            </div>

            <button type="submit" name="register" class="btn btn-custom w-100">Register</button>
        </form>
        <p class="toggle-link" onclick="toggleForms()">Already have an account? Login here</p>
    </div>

    <!-- Login Form -->
    <div id="loginForm" class="card" style="display: none;">
        <h5 class="text-center text-primary">Login</h5>
        <form method="POST">
            <input type="email" name="email" class="form-control mb-2" placeholder="Email Address" required>
            
            <div class="password-container mb-2">
                <input type="password" id="loginPassword" name="password" class="form-control" placeholder="Password" required>
                <span class="toggle-password" onclick="togglePassword('loginPassword')">👁️</span>
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100">Login</button>
        </form>
        <p class="toggle-link" onclick="toggleForms()">Don't have an account? Register here</p>
    </div>
</div>

<script>
    function toggleForms() {
        document.getElementById("registerForm").style.display = 
            document.getElementById("registerForm").style.display === "none" ? "block" : "none";
        document.getElementById("loginForm").style.display = 
            document.getElementById("loginForm").style.display === "none" ? "block" : "none";
    }

    function togglePassword(id) {
        let field = document.getElementById(id);
        field.type = field.type === "password" ? "text" : "password";
    }

    setTimeout(() => {
        let alertMessage = document.getElementById("alertMessage");
        if (alertMessage) alertMessage.style.display = "none";
    }, 6000);
</script>

</body>
</html>
