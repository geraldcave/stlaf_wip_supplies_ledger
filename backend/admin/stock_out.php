<?php
session_start();
require_once '../../sql/config.php';
require_once 'logics/stock_out.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$stock = new StockOut($conn);
$ledger = $stock->getLedger();

$firstname = ucfirst($_SESSION['username'] ?? 'Admin');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | Stock Out Ledger</title>
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../../assets/images/sub_logo_light.png">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/super.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <div class="d-flex">
        <?php include "includes/sidebar.php"; ?>

        <div class="main">
            <?php include "includes/topbar.php"; ?>

            <div style="width:95%; margin:20px auto; background:#f8f9fa; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.08);">
                <div class="card shadow-lg border-0 p-4 rounded-4" style="height: 83vh;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <h3 class="fw-bold m-0">Stock Out Ledger</h3>
                        <input type="text" id="searchInput" class="form-control" style="max-width: 300px;" placeholder="🔍 Search item...">
                    </div>

                    <div class="table-container p-4 shadow-sm rounded-4 bg-white">
                        <div class="table-responsive" style="max-height:600px; overflow-y:auto;">
                            <table class="table table-hover align-middle mb-0" id="ledgerTable">
                                <thead class="text-center">
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty Out</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $ledger->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['description']) ?></td>
                                            <td class="text-center"><?= $row['qty_out'] ?></td>
                                            <td class="text-center"> <?= $row['date_out'] ?? '' ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../../assets/bootstrap/all.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const toggler = document.querySelector(".toggler-btn");
        toggler.addEventListener("click", function() {
            document.querySelector("#sidebar").classList.toggle("collapsed");
        });

        // Search filter
        $(document).ready(function() {
            $("#searchInput").on("keyup", function() {
                var value = $(this).val().toLowerCase();
                $("#ledgerTable tbody tr").filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });
        });
    </script>
</body>

</html>