<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Midtrans\Config;
use Midtrans\Snap;

// Konfigurasi Midtrans
Config::$serverKey = 'SB-Mid-server-jGwdUeyFWGE4Kz-HQ4uhexCY';
Config::$isProduction = false; // Ubah ke true jika di production
Config::$isSanitized = true;
Config::$is3ds = true;

// Pastikan tidak ada output HTML sebelum ini
ob_start(); // Memulai buffer output agar tidak ada output HTML

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['transId'])) {
        $transId = $_POST['transId']; // Ambil transId dari form
        $userId = $_SESSION['userId']; // Ambil user ID dari session

        try {
            // Ambil data transaksi berdasarkan transId
            $query = "SELECT p.productId, p.quantity, p.amountTotal, u.fullName, u.email, u.phone
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

            // Kirim Snap Token ke frontend
            echo json_encode(['success' => true, 'snapToken' => $snapToken]);

            // Update status transaksi jika pembayaran berhasil
            if (isset($_POST['snapToken'])) {
                $query = "UPDATE purchases SET status = 'paid' WHERE transId = ?";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$transId]);
            }

        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'transId tidak diterima']);
    }
}

ob_end_flush(); // Mengirim buffer output
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran</title>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="YOUR_CLIENT_KEY"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            display: block;
            width: 100%;
            padding: 10px;
            font-size: 18px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Pembayaran</h1>
        <form id="payment-form">
            <div class="form-group">
                <label for="transId">Transaction ID</label>
                <input type="text" id="transId" name="transId" value="<?php echo $_GET['transId']; ?>" required readonly>
            </div>
            <button type="button" id="pay-button">Bayar Sekarang</button>
        </form>
    </div>

    <script>
       document.getElementById('pay-button').addEventListener('click', function () {
    const transId = document.getElementById('transId').value;

    if (!transId) {
        alert('Transaction ID tidak boleh kosong!');
        return;
    }

    fetch('payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ transId: transId })
    })
    .then(response => response.json()) // Pastikan respons JSON diparse dengan benar
    .then(data => {
        console.log(data); // Log data untuk pengecekan
        if (data.success) {
            snap.pay(data.snapToken, {
                onSuccess: function(result) {
                    alert('Pembayaran berhasil!');
                    // Update status transaksi langsung di sini
                    fetch('payment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ transId: transId, status: 'paid' })
                    }).then(response => response.json())
                      .then(data => {
                          if (data.success) {
                              alert('Status transaksi berhasil diperbarui!');
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
        } else {
            alert('Gagal mendapatkan Snap Token: ' + data.message);
        }
    })
    .catch(error => console.error('Error:', error));
});

    </script>
</body>
</html>
