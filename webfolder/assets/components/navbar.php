<?php
// Cek apakah user sudah login
if (isset($_SESSION['userId'])) {
    $userId = $_SESSION['userId'];

    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    if ($row) {
        $firstName = $row['fullName'] ? explode(' ', $row['fullName'])[0] : 'User';
        $profilePicture = (!empty($row['img']) && file_exists($row['img'])) ? $row['img'] : 'default-avatar.jpg';

        // Cek apakah user adalah seller
        $querySeller = "SELECT sellerId FROM sellers WHERE userId = ?";
        $stmtSeller = $pdo->prepare($querySeller);
        $stmtSeller->execute([$userId]);
        $sellers = $stmtSeller->fetch();
        
        if ($sellers) {
            $_SESSION['role'] = 'seller';
            $_SESSION['sellerId'] = $sellers['sellerId'];
        } else {
            $_SESSION['role'] = 'user';
        }
    }
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand mt-1" href="#home"><h2>ACHATS</h2></a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarTogglerDemo02">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" href="#categories"><h6>Categories</h6></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#products"><h6>Products</h6></a>
                </li>
            </ul>

            <?php if (isset($_SESSION['userId'])): ?>
                <div class="dropdown">
                    <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="me-2"><?= $firstName; ?></span>
                        <img src="<?= $profilePicture; ?>" alt="Profile" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#">Hello, <?= $firstName; ?></a></li>
                        <?php if ($_SESSION['role'] === 'seller'): ?>
                            <li><a class="dropdown-item" href="sellers">Seller Dashboard</a></li>
                        <?php endif; ?>
                        <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                        <li><a class="dropdown-item" href="purchase.php">Pembelian</a></li>
                        <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="config/functions/logout.php">Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-success">Login</a>
                <a href="registration.php" class="btn btn-success ms-2">Registration</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
