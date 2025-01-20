<?php
session_start();
require_once 'config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (!empty($email) && !empty($password)) {
        // Query untuk mendapatkan data pengguna
        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Cek apakah pengguna memiliki sellerId di tabel sellers
            $sellerQuery = "SELECT sellerId FROM sellers WHERE userId = ?";
            $sellerStmt = $pdo->prepare($sellerQuery);
            $sellerStmt->execute([$user['id']]);
            $seller = $sellerStmt->fetch();

            // Set peran berdasarkan hasil pencarian sellerId
            $role = $seller ? 'seller' : 'user';

            // Set sesi pengguna
            $_SESSION['userId'] = $user['id'];
            $_SESSION['fullName'] = $user['fullName'];
            $_SESSION['role'] = $role;
            $_SESSION['email'] = $user['email'];

            if ($role === 'seller') {
                // Redirect ke dashboard seller
                $_SESSION['sellerID'] = $seller['sellerId'];
                
                header('Location: index.php');
            } else {
                // Redirect ke dashboard umum atau pembeli
                header('Location: index.php');
            }
            exit();
        } else {
            $error = "Email atau password salah.";
        }
    } else {
        $error = "Mohon isi semua kolom.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4" style="width: 400px;">
            <h1 class="text-center mb-4">Login</h1>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <p class="mt-3 text-center">Belum punya akun? <a href="registration.php">Daftar di sini</a></p>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
