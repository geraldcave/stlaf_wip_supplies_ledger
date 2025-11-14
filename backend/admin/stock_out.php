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
        <aside id="sidebar" class="sidebar-toggle">
            <div class="sidebar-logo mt-3">
                <img src="../../assets/images/official_logo.png" width="80px" height="80px">
            </div>
            <div class="menu-title">Navigation</div>

            <li class="sidebar-item">
                <a href="admin_dashboard.php" class="sidebar-link">
                    <i class="bi bi-cast"></i>
                    <span style="font-size: 18px;">Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="req_tab.php" class="sidebar-link active">
                    <i class="bi bi-box"></i>
                    <span style="font-size: 18px;">Employee Requests</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="ins_form.php" class="sidebar-link active">
                    <i class="bi bi-basket"></i>
                    <span style="font-size: 18px;">Ins Forms</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="stock_in.php" class="sidebar-link active">
                    <i class="bi bi-basket"></i>
                    <span style="font-size: 18px;">Stock In</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="stock_out.php" class="sidebar-link active">
                    <i class="bi bi-basket"></i>
                    <span style="font-size: 18px;">Deducted Items</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="inventory_dashboard.php" class="sidebar-link">
                    <i class="bi bi-speedometer2"></i>
                    <span style="font-size: 18px;">Supply Tracking</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="config_item.php" class="sidebar-link active"><i class="bi bi-gear"></i>
                    <span>Configuration</span></a>
            </li>
            <li class="sidebar-item">
                <a href="summary.php" class="sidebar-link active"><i class="bi bi-clipboard-data"></i></i>
                    <span>Summary</span></a>
            </li>
            <li class="sidebar-item">
                <a href="../../logout.php" class="sidebar-link">
                    <i class="bi bi-box-arrow-right"></i>
                    <span style="font-size: 18px;">Logout</span>
                </a>
            </li>
        </aside>

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <button class="toggler-btn" type="button"><i class="bi bi-list-ul" style="font-size: 28px;"></i></button>
                </div>
                <div class="logo d-flex align-items-center">
                    <span class="username me-2 fw-bold text-primary"><?= htmlspecialchars($firstname) ?> (Admin)</span>
                </div>
            </div>

            <div style="width:95%; margin:20px auto; background:#f8f9fa; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.08);">
                <div class="card shadow-lg border-0 p-4 rounded-4">
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