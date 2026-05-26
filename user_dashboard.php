<?php
session_start();
require_once 'config.php';

/* ========== SECURITY CHECK ========== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

/* ========== FETCH USER INFO ========== */
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Medical Clinic | User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .profile-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #0d6efd;
        }
    </style>
</head>
<body>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>Welcome, <?= htmlspecialchars($user['full_name']) ?> 👋</h3>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body text-center">
            <?php if(isset($user['photo']) && $user['photo'] != ''): ?>
                <img src="uploads/<?= $user['photo'] ?>" alt="Profile Photo" class="profile-img mb-3">
            <?php else: ?>
                <div class="profile-img bg-secondary d-inline-block mb-3"></div>
            <?php endif; ?>

            <h5><?= htmlspecialchars($user['full_name']) ?></h5>
            <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
            <span class="badge bg-primary"><?= ucfirst($user['role']) ?></span>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    📅 Upcoming Appointments
                </div>
                <div class="card-body">
                    <p>No appointments scheduled yet.</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    🔔 Notifications
                </div>
                <div class="card-body">
                    <p>No new notifications.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
