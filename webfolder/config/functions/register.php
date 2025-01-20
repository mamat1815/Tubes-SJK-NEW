<?php
session_start();
include('../config.php');
global $pdo;

// Fungsi untuk menangani upload file
function uploadFile($file, $uploadDir) {
    if ($file['tmp_name']) {
        $fileName = basename($file['name']);
        $targetPath = $uploadDir . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return 'uploads/' . $fileName;
        }
    }
    return null;
}

// Validasi data input
function validateInput($data, $fieldName) {
    if (empty($data)) {
        throw new Exception("$fieldName tidak boleh kosong.");
    }
    return htmlspecialchars(trim($data));
}

try {
    // Ambil data dari form
    $fullname = validateInput($_POST['fullname'], 'Nama lengkap');
    $email = validateInput($_POST['email'], 'Email');
    $password = password_hash(validateInput($_POST['password'], 'Password'), PASSWORD_BCRYPT);
    $address = validateInput($_POST['address'], 'Alamat');
    $phone = validateInput($_POST['phone'], 'Nomor telepon');

    // Direktori untuk menyimpan file
    $uploadDir = '../../uploads';
    $profilePicture = uploadFile($_FILES['profilePicture'], $uploadDir);

    // Insert data user ke tabel users
    $sqlUser = "INSERT INTO users (fullName, email, password, address, img, number) VALUES (?, ?, ?, ?, ?, ?)";
    $stmtUser = $pdo->prepare($sqlUser);
    $stmtUser->execute([$fullname, $email, $password, $address, $profilePicture, $phone]);

    $userId = $pdo->lastInsertId();

    // Simpan data user ke session
    $_SESSION['userId'] = $userId;
    $_SESSION['fullname'] = $fullname;
    $_SESSION['role'] = 'user'; // Default sebagai user

    // Jika user adalah seller
    if (isset($_POST['isSeller']) && $_POST['isSeller'] === 'on') {
        $bio = validateInput($_POST['bio'], 'Bio');
        $storePicture = uploadFile($_FILES['storePicture'], $uploadDir);
        $skills = isset($_POST['skills']) ? explode(',', validateInput($_POST['skills'], 'Skills')) : [];

        // Default values
        $available = true; // Default available
        $hourRate = 50; // Default hourly rate

        // Insert data seller ke tabel sellers
        $sqlSeller = "INSERT INTO sellers (userId, bio, img, rate, availability, hourRate) VALUES (?, ?, ?, 0, ?, ?)";
        $stmtSeller = $pdo->prepare($sqlSeller);
        $stmtSeller->execute([$userId, $bio, $storePicture, $available, $hourRate]);

        $sellerId = $pdo->lastInsertId();

        // Tambahkan skills ke tabel skills dan sellerSkill
        foreach ($skills as $skillName) {
            $skillName = trim($skillName);

            // Cek apakah skill sudah ada
            $sqlCheckSkill = "SELECT skillId FROM skills WHERE skillValue = ?";
            $stmtCheckSkill = $pdo->prepare($sqlCheckSkill);
            $stmtCheckSkill->execute([$skillName]);
            $resultCheckSkill = $stmtCheckSkill->fetch(PDO::FETCH_ASSOC);

            if ($resultCheckSkill) {
                $skillId = $resultCheckSkill['skillId'];
            } else {
                // Tambahkan skill baru
                $sqlAddSkill = "INSERT INTO skills (skillValue) VALUES (?)";
                $stmtAddSkill = $pdo->prepare($sqlAddSkill);
                $stmtAddSkill->execute([$skillName]);

                $skillId = $pdo->lastInsertId();
            }

            // Hubungkan seller dengan skill
            $sqlSellerSkill = "INSERT INTO sellerskill (sellerId, skillId) VALUES (?, ?)";
            $stmtSellerSkill = $pdo->prepare($sqlSellerSkill);
            $stmtSellerSkill->execute([$sellerId, $skillId]);
        }

        // Tambahkan entri awal di tabel transactions
        $insertTransactionQuery = "INSERT INTO transactions (sellerId, status, amount, created_at) VALUES (?, 'initialized', 0, NOW())";
        $stmtTransaction = $pdo->prepare($insertTransactionQuery);
        $stmtTransaction->execute([$sellerId]);

        // Update session untuk seller
        $_SESSION['role'] = 'seller';
        $_SESSION['sellerId'] = $sellerId;

        // Redirect ke dashboard seller
        header('Location: ../../sellers');
        exit();
    } else {
        // Redirect ke dashboard user
        header('Location: ../../');
        exit();
    }
} catch (Exception $e) {
    // Tampilkan pesan error
    echo "Error: " . $e->getMessage();
}
?>
