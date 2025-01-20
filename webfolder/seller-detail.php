<?php
session_start();
require_once 'config/config.php';

// Get sellerId from URL
$sellerId = $_GET['id'] ?? null;
if (!$sellerId) {
    echo "Seller ID is missing.";
    exit;
}


    // Fetch seller details
    $sellerQuery = "
        SELECT 
            u.fullName, 
            s.hourRate, 
            s.availability, 
            s.rate, 
            s.bio, 
            s.img 
        FROM 
            sellers s 
        JOIN 
            users u 
        ON 
            s.userId = u.id 
        WHERE 
            s.sellerId = :sellerId
    ";
    $stmt = $pdo->prepare($sellerQuery);
    $stmt->execute(['sellerId' => $sellerId]);

    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$seller || !isset($seller['fullName'])) {
        echo "Data seller tidak ditemukan atau incomplete.";
        exit;
    }
    
    if (!$seller) {
        throw new Exception("Seller tidak ditemukan");
    }

    // Fetch seller skills
    $skillsQuery = "
        SELECT 
            sk.skillValue 
        FROM 
            sellerskill ss 
        JOIN 
            skills sk 
        ON 
            ss.skillId = sk.skillId 
        WHERE 
            ss.sellerId = :sellerId
    ";
    $stmtSkills = $pdo->prepare($skillsQuery);
    $stmtSkills->execute(['sellerId' => $sellerId]);
    $sellerSkills = $stmtSkills->fetchAll(PDO::FETCH_COLUMN);

    // Fetch work history (completed and in-progress)
    $workHistoryQuery = "
        SELECT 
            o.name AS title, 
            o.status, 
            o.price, 
            o.workTime, 
            o.deadline, 
            o.description 
        FROM 
            orders o 
        WHERE 
            o.sellerId = :sellerId
    ";
    $stmtWorkHistory = $pdo->prepare($workHistoryQuery);
    $stmtWorkHistory->execute(['sellerId' => $sellerId]);
    $workHistory = $stmtWorkHistory->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Seller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/0444f7d0d8.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <?php require 'assets/components/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="row">
            <!-- Seller Header -->
            <div class="col-12">
                <div class="card p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <img src="<?= htmlspecialchars($seller['img'] ?? 'https://via.placeholder.com/100') ?>" 
                                 alt="Seller Image" class="rounded-circle me-3" 
                                 style="width: 100px; height: 100px; object-fit: cover;">
                            <div>
                                
                                <h3 class="mb-0"><?= $seller['fullName'] ?></h3>
                                <div class="d-flex align-items-center mt-2">
                                    <span class="badge bg-success me-2">100% Job Success</span>
                                    <span class="badge bg-primary">Top Rated</span>
                                </div>
                            </div>
                        </div>
                        <a href="order.php?sellerId=<?= htmlspecialchars($sellerId) ?>" 
   class="btn btn-primary btn-lg" 
   onclick="return confirmLogin()">Order Jasa</a>

                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-12 mt-4">
                <div class="row g-4">
                    <!-- Left Column -->
                    <div class="col-md-4">
                        <!-- Skills Section -->
                        <div class="card">
                            <div class="card-body">
                                <h5>Keahlian</h5>
                                <ul class="list-unstyled">
                                    <?php foreach ($sellerSkills as $skill): ?>
                                        <span class="badge bg-primary me-1"><?= htmlspecialchars($skill) ?></span>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>

                        <!-- Availability -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <h5>Jam Kerja Per Minggu</h5>
                                <p class="mb-0"><?= htmlspecialchars($seller['availability'] ?? 'Tidak tersedia') ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-8">
                        <!-- About Section -->
                        <div class="card">
                            <div class="card-body">
                                <h5>Profil</h5>
                                <p><?= htmlspecialchars($seller['bio']) ?></p>
                                <h6>Tarif: Rp<?= number_format($seller['hourRate'] ?? 0, 0, ',', '.') ?></h6>
                            </div>
                        </div>

                        <!-- Work History -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <h5>Riwayat Kerja</h5>
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="completed-jobs-tab" data-bs-toggle="tab" data-bs-target="#completed-jobs" type="button" role="tab" aria-controls="completed-jobs" aria-selected="true">Pekerjaan Selesai</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="in-progress-tab" data-bs-toggle="tab" data-bs-target="#in-progress" type="button" role="tab" aria-controls="in-progress" aria-selected="false">Sedang Berlangsung</button>
                                    </li>
                                </ul>
                                <div class="tab-content mt-3" id="myTabContent">
                                    <div class="tab-pane fade show active" id="completed-jobs" role="tabpanel" aria-labelledby="completed-jobs-tab">
                                        <?php foreach ($workHistory as $work): ?>
                                            <?php if ($work['status'] === 'completed'): ?>
                                                <h6><?= htmlspecialchars($work['title']) ?></h6>
                                                <p><i class="fa fa-star text-warning"></i> Rp<?= number_format($work['price'], 0, ',', '.') ?> | <?= htmlspecialchars($work['workTime']) ?> jam | <?= htmlspecialchars($work['description']) ?></p>
                                                <p>Deadline: <?= htmlspecialchars($work['deadline']) ?></p>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="tab-pane fade" id="in-progress" role="tabpanel" aria-labelledby="in-progress-tab">
                                        <?php foreach ($workHistory as $work): ?>
                                            <?php if ($work['status'] === 'in-progress'): ?>
                                                <h6><?= htmlspecialchars($work['title']) ?></h6>
                                                <p>Sedang Berlangsung</p>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
function confirmLogin() {
    <?php if (!isset($_SESSION['userId'])): ?>
        alert('Anda harus login terlebih dahulu untuk melakukan order.');
        window.location.href = 'login.php';
        return false;
    <?php endif; ?>
    return true;
}
</script>

</body>
</html>
