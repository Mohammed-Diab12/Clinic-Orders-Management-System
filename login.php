<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email    = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare(
        "SELECT id, full_name, password, role FROM users WHERE email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

       
        if ($password === $user['password']) {

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['full_name'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: user_dashboard.php");
            }
            exit;
        }
    }

    $_SESSION['error'] = "Invalid email or password";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Clinic | Login</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <style>
        body {
            background: linear-gradient(to right, #0d6efd, #6f42c1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            border-radius: 15px;
            overflow: hidden;
        }

        .login-left {
            background: #0d6efd;
            color: white;
            padding: 40px;
        }

        .login-right {
            padding: 40px;
        }

        .clinic-icon {
            font-size: 60px;
        }
    </style>
</head>

<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">

            <div class="card login-card shadow-lg">
                <div class="row g-0">

                    <div class="col-md-5 login-left text-center d-flex flex-column justify-content-center">
                        <div class="clinic-icon mb-3">🏥</div>
                        <h3>Medical Clinic</h3>
                        <p class="mt-3">
                            Secure Clinic Management System
                        </p>
                    </div>

                    <div class="col-md-7 login-right">

                        <h4 class="mb-4 text-center">Login to Your Account</h4>

                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger text-center">
                                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST">

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" min="5" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                Login
                            </button>

                        </form>

                        <div class="text-center mt-3">
                            <small class="text-muted">
                                Medical Clinic System © 2025
                            </small>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

</body>
</html>
