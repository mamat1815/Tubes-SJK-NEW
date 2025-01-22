<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION)) {
    echo "User not logged in.";
    exit();
}

$user_id = $_SESSION['userId'];

require __DIR__ . '/../config/functions/fetch-seller.php';

$seller = fetchSeller($user_id);

$userQuery = "SELECT * FROM users WHERE id = ?";
$stmtUser = $pdo->prepare($userQuery);
$stmtUser->execute([$user_id]);
$user = $stmtUser->fetch();

// Fetch transactions based on status
$statuses = ['pending', 'successful', 'failed'];
$transactions = [];

foreach ($statuses as $status) {
    $transactionsQuery = "SELECT * FROM purchases WHERE userId = ? AND status = ?";
    $stmt = $pdo->prepare($transactionsQuery);
    $stmt->execute([$user_id, $status]);
    $transactions[$status] = $stmt->fetchAll();
}

// Fetch product details for each transaction
$productDetails = [];
foreach ($transactions as $status => $trans) {
    foreach ($trans as $transaction) {
        $productId = $transaction['productId'];
        $productQuery = "SELECT * FROM products WHERE productId = ?";
        $stmtProduct = $pdo->prepare($productQuery);
        $stmtProduct->execute([$productId]);
        $productDetails[$transaction['transId']] = $stmtProduct->fetch();
    }
}

// Update transactions with product details
foreach ($transactions as $status => &$trans) {
    foreach ($trans as &$transaction) {
        $transaction['productName'] = $productDetails[$transaction['transId']]['name'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Seller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/seller.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/charts.css/dist/charts.min.css">
</head>
<body>
    <div class="sidebar">
        <a href="../"><h2>Dashboard Profile</h2></a>
        <a href="index.php" class="active">Dashboard</a>
        <a href="chats.php">Chat</a>
        <a href="cart.php">Keranjang</a>
        <a href="config/functions/logout.php">Logout</a>
    </div>

    <div class="main-content">
        <h1>Welcome, <?php echo htmlspecialchars($user['fullName']); ?></h1>
        <h2>Transaction History</h2>
        <ul class="nav nav-tabs" id="transactionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab" aria-controls="pending" aria-selected="true">Pending</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="successful-tab" data-bs-toggle="tab" data-bs-target="#successful" type="button" role="tab" aria-controls="successful" aria-selected="false">Successful</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="failed-tab" data-bs-toggle="tab" data-bs-target="#failed" type="button" role="tab" aria-controls="failed" aria-selected="false">Failed</button>
            </li>
        </ul>
        <div class="tab-content" id="transactionTabsContent">
            <?php foreach ($statuses as $status): ?>
                <div class="tab-pane fade <?php echo $status === 'pending' ? 'show active' : ''; ?>" id="<?php echo $status; ?>" role="tabpanel" aria-labelledby="<?php echo $status; ?>-tab">
                    <table class="table table-striped">
                        <thead>
                        <tr>
                            <th data-sort="transId">Transaction ID</th>
                            <th data-sort="productName">Product Name</th>
                            <th data-sort="amountTotal">Amount</th>
                            <th data-sort="purchaseDate">Date</th>
                            <?php if ($status === 'pending'): ?>
                                <th>Action</th>
                            <?php endif; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($transactions[$status] as $transaction): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($transaction['transId']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['productName']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['amountTotal']); ?></td>
                                <td><?php echo htmlspecialchars($transaction['purchaseDate']); ?></td>
                                <?php if ($status === 'pending'): ?>
                                    <td>
                                        <a href="payment.php?transId=<?php echo htmlspecialchars($transaction['transId']); ?>" class="btn btn-primary">Pay</a>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterInput = document.createElement('input');
        filterInput.setAttribute('type', 'text');
        filterInput.setAttribute('placeholder', 'Filter transactions...');
        filterInput.classList.add('form-control', 'mb-3');

        const tabsContent = document.getElementById('transactionTabsContent');
        tabsContent.insertBefore(filterInput, tabsContent.firstChild);

        filterInput.addEventListener('input', function () {
            const filterValue = filterInput.value.toLowerCase();
            const tables = tabsContent.querySelectorAll('table');

            tables.forEach(table => {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const match = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(filterValue));
                    row.style.display = match ? '' : 'none';
                });
            });
        });

        const getCellValue = (row, index) => row.children[index].innerText || row.children[index].textContent;

        const comparer = (index, asc) => (a, b) => ((v1, v2) =>
            v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2)
        )(getCellValue(asc ? a : b, index), getCellValue(asc ? b : a, index));

        document.querySelectorAll('th[data-sort]').forEach(th => th.addEventListener('click', (() => {
            const table = th.closest('table');
            Array.from(table.querySelectorAll('tbody > tr'))
                .sort(comparer(Array.from(th.parentNode.children).indexOf(th), this.asc = !this.asc))
                .forEach(tr => table.querySelector('tbody').appendChild(tr));
        })));
    });
    </script>
</body>
</html>