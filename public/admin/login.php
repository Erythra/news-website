<?php
require_once '../../app/db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = connectMongoDB();
    $collection = $db->admin_login;

    $username = $_POST['username'];
    $password = trim($_POST['password']); // User input password

    // Fetch the admin from MongoDB based on username
    $admin = $collection->findOne(['username' => $username]);

    // Directly compare the password (no hashing)
    if ($admin && $password === $admin['password']) {
        $_SESSION['admin'] = $admin['username'];
        header("Location: ./dashboard.php");
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
        include '../partials/cdn.php';
    ?>
    <title>Admin Login</title>
</head>

<body>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST">
        <div class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background-color: #f8f9fa;">
            <div class="card p-5 shadow-lg" style="width: 28rem;">
                <div class="text-center mb-4 text-uppercase fw-bold fs-2">Polinews Login</div>
                <form method="POST">
                    <input type="text" class="form-control border-0 border-bottom border-1 rounded-0 border-dark mb-3" id="username" name="username" placeholder="Nama Pengguna" required>
                    <input type="password" class="form-control border-0 border-bottom border-1 rounded-0 border-dark mb-3" id="password" name="password" placeholder="Kata Sandi" required>
                    <div class="form-check mb-4 d-flex justify-content-between" style="font-size: small;">
                        <div class="justify-content-start">
                            <input class="form-check-input" type="checkbox" value="" id="remindCheckBox">
                            <label class="form-check-label text-muted" for="remindCheckBox">
                                Ingat saya
                            </label>
                        </div>
                        <div class="text-end">
                            <a href="#" class="text-decoration-none text-muted">Lupa Kata Sandi?</a>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-dark w-100 rounded-0">Masuk</button>
                </form>
            </div>
              
        </div>

    </form>
</body>

</html>