<?php
session_start();
require_once 'config.php';

/* SECURITY CHECK  */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

/*  ADD USER  */
if (isset($_POST['add_user'])) {

    $name     = $_POST['full_name'];
    $email    = $_POST['email'];
    $password = $_POST['password'];   // plain text
    $role     = $_POST['role'];

    $photoName = "";
    if (!empty($_FILES['photo']['name'])) {
        $photoName = time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photoName);
    }

    $stmt = $conn->prepare(
        "INSERT INTO users (full_name, email, password, role, photo)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssss", $name, $email, $password, $role, $photoName);
    $stmt->execute();
}

/*  DELETE USER  */
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    

    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

/*  UPDATE USER  */
if (isset($_POST['update_user'])) {

    $id    = $_POST['id'];
    $name  = $_POST['full_name'];
    $email = $_POST['email'];
    $role  = $_POST['role'];
    $pass  = $_POST['password'];

    // fetch old photo
    $res = $conn->query("SELECT photo FROM users WHERE id=$id");
    $row = $res->fetch_assoc();
    $photoName = $row['photo'];

    // if new photo uploaded, replace it
    if (!empty($_FILES['photo']['name'])) {
        if($photoName && file_exists("uploads/".$photoName)) {
            unlink("uploads/".$photoName); // delete old photo
        }
        $photoName = time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], "uploads/" . $photoName);
    }

    if (!empty($pass)) {
        $stmt = $conn->prepare(
            "UPDATE users SET full_name=?, email=?, role=?, password=?, photo=? WHERE id=?"
        );
        $stmt->bind_param("sssssi", $name, $email, $role, $pass, $photoName, $id);
    } else {
        $stmt = $conn->prepare(
            "UPDATE users SET full_name=?, email=?, role=?, photo=? WHERE id=?"
        );
        $stmt->bind_param("sss si", $name, $email, $role, pass, $photoName, $id);
    }

    $stmt->execute();
}

/*  FETCH USERS  */
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin | Manage Users</title>
    <link   rel="stylesheet"   href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-4">

    <div class="d-flex justify-content-between mb-3">
        <h3>👨‍⚕️ Medical Clinic – Admin Dashboard</h3>
        <a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
    </div>

    <!--  ADD USER FORM  -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Add New Patient
        </div>
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="full_name" class="form-control" placeholder="Full Name" required>
                </div>
                <div class="col-md-3">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                </div>
                <div class="col-md-2">
                    <input type="password" name="password" class="form-control" placeholder="Password" min="5" required>
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="file" name="photo" class="form-control">
                </div>
                <div class="col-md-12">
                    <button name="add_user" class="btn btn-success w-100">
                        Add Patient
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!--  USERS TABLE  -->
    <div class="card">
        <div class="card-header bg-dark text-white">
            Patients List
        </div>
        <div class="card-body p-0">
            <table class="table table-striped text-center mb-0">
                <thead class="table-secondary">
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td>
                            <?php if($row['photo']): ?>
                                <img src="uploads/<?= $row['photo'] ?>" width="40" height="40" class="rounded-circle">
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= $row['full_name'] ?></td>
                        <td><?= $row['email'] ?></td>
                        <td><?= $row['role'] ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm"
                                data-bs-toggle="modal"
                                data-bs-target="#editUser<?= $row['id'] ?>">
                                Edit
                            </button>

                            <a href="?delete=<?php echo $row['id']; ?>"
                               onclick="return confirm('Delete this user?')"
                               class="btn btn-danger btn-sm">
                                Delete
                            </a>
                        </td>
                    </tr>

                    <!--  EDIT USER MODAL  -->
                    <div class="modal fade" id="editUser<?= $row['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">

                                <form method="POST" enctype="multipart/form-data">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit User</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">

                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">

                                        <div class="mb-2">
                                            <label>Full Name</label>
                                            <input type="text" name="full_name"
                                                value="<?= $row['full_name'] ?>"
                                                class="form-control" required>
                                        </div>

                                        <div class="mb-2">
                                            <label>Email</label>
                                            <input type="email" name="email"
                                                value="<?= $row['email'] ?>"
                                                class="form-control" required>
                                        </div>

                                        <div class="mb-2">
                                            <label>Role</label>
                                            <select name="role" class="form-select">
                                                <option value="user" <?= $row['role']=='user'?'selected':'' ?>>User</option>
                                                <option value="admin" <?= $row['role']=='admin'?'selected':'' ?>>Admin</option>
                                            </select>
                                        </div>

                                       <div class="mb-2">
                                        <label>Password</label>
                                        <input type="text" name="password" value="<?= $row['password'] ?>" class="form-control" required>
                                        </div>

                                        <div class="mb-2">
                                            <label>Change Photo (leave empty to keep old)</label>
                                            <input type="file" name="photo" class="form-control">
                                            <?php if($row['photo']): ?>
                                                <img src="uploads/<?= $row['photo'] ?>" width="60" height="60" class="rounded-circle mt-2">
                                            <?php endif; ?>
                                        </div>

                                    </div>

                                    <div class="modal-footer">
                                        <button type="submit" name="update_user" class="btn btn-success">
                                            Save Changes
                                        </button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            Cancel
                                        </button>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
                </tbody>

            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
