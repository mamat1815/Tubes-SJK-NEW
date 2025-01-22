<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Midtrans\Config;
use Midtrans\Snap;

Config::$serverKey = 'SB-Mid-server-jGwdUeyFWGE4Kz-HQ4uhexCY';
Config::$isProduction = false; // Jika di production, set ke true
Config::$isSanitized = true;
Config::$is3ds = true;

// Cek jika request menggunakan method GET dan transId ada di URL
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['transId'])) {
    $transId = $_GET['transId'];
    // Ambil data transaksi berdasarkan transId
    try {
        $query = "SELECT p.productId, p.quantity, p.amountTotal, u.fullName, u.email, u.number
                  FROM purchases p
                  JOIN users u ON p.userId = u.id
                  WHERE p.transId = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transId]);
        $purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($purchases)) {
            throw new Exception("Transaksi tidak ditemukan.");
        }

        // Hitung total amount
        $grossAmount = array_sum(array_column($purchases, 'amountTotal'));

        // Siapkan item details untuk Midtrans
        $itemDetails = [];
        foreach ($purchases as $purchase) {
            $itemDetails[] = [
                'id' => $purchase['productId'],
                'price' => $purchase['amountTotal'] / $purchase['quantity'],
                'quantity' => $purchase['quantity'],
                'name' => 'Product ' . $purchase['productId']
            ];
        }

        // Data transaksi untuk Midtrans
        $transactionDetails = [
            'transaction_details' => [
                'order_id' => $transId,
                'gross_amount' => $grossAmount
            ],
            'customer_details' => [
                'first_name' => $purchases[0]['fullName'],
                'email' => $purchases[0]['email'],
                'phone' => $purchases[0]['phone']
            ],
            'item_details' => $itemDetails
        ];

        // Buat Snap Token
        $snapToken = Snap::getSnapToken($transactionDetails);
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// Proses pembaruan status transaksi (POST request)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transId'])) {
    $transId = $_POST['transId'];
    try {
        // Update status transaksi menjadi 'paid'
        $query = "UPDATE purchases SET status = 'paid' WHERE transId = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$transId]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Status transaksi berhasil diperbarui!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Tidak ada transaksi dengan ID tersebut!']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran</title>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="YOUR_CLIENT_KEY"></script>
    <style>
        /* Style CSS yang telah diatur sebelumnya */
    </style>
</head>
<body>
    <div class="container">
        <h1>Pembayaran</h1>

        <?php if (isset($snapToken)): ?>
            <!-- Jika Snap Token tersedia, tampilkan tombol bayar -->
            <form id="payment-form">
                <div class="form-group">
                    <label for="transId">Transaction ID</label>
                    <input type="text" id="transId" name="transId" value="<?= htmlspecialchars($transId) ?>" readonly>
                </div>
                <button type="button" id="pay-button">Bayar Sekarang</button>
            </form>
        <?php elseif (isset($errorMessage)): ?>
            <p style="color: red;"><?= htmlspecialchars($errorMessage) ?></p>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('pay-button').addEventListener('click', function () {
            const transId = document.getElementById('transId').value;

            if (!transId) {
                alert('Transaction ID tidak boleh kosong!');
                return;
            }

            // Mengirim Snap Token ke Midtrans untuk melakukan pembayaran
            snap.pay("<?php echo $snapToken; ?>", {
                onSuccess: function(result) {
                    alert('Pembayaran berhasil!');

                    // Setelah pembayaran berhasil, update status transaksi ke "paid"
                    fetch('payment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ transId: transId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Status transaksi berhasil diperbarui!');
                        } else {
                            alert('Gagal memperbarui status transaksi: ' + data.message);
                        }
                    });
                },
                onPending: function(result) {
                    alert('Pembayaran pending!');
                },
                onError: function(result) {
                    alert('Pembayaran gagal!');
                }
            });
        });
    </script>
</body>
</html>
