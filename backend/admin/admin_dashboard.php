<?php
session_start();
require_once '../../sql/config.php';
require_once "../../auth/oop/request_form.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$ledger = $conn->query("
    SELECT 'IN' AS type, i.description, s.qty_in AS qty, s.date_in AS date, s.remarks
    FROM stock_in s
    JOIN items i ON s.item_id = i.id
    UNION ALL
    SELECT 'OUT', i.description, s.qty_out, s.date_out, s.remarks
    FROM stock_out s
    JOIN items i ON s.item_id = i.id
    ORDER BY date DESC
");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['req_id'] ?? $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null;
    $reason = $_POST['reason'] ?? null;

    if ($id && $action === 'approve') {
        // First step: just approve
        $request->updateStatus($id, 'Approved');
    } elseif ($id && $action === 'deliver') {
        // Second step: delivery
        $request->updateStatus($id, 'Delivered');
    } elseif ($id && $reason) {
        // If cancelled
        $request->updateStatus($id, 'Cancelled', $reason);
    }
}

$requests = $request->getAllRequests();
$firstname = ucfirst($_SESSION['username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | Dashboard</title>
    <link rel="icon" type="image/png" href="../../assets/images/sub_logo_light.png">
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/super.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <div class="d-flex">
        <aside id="sidebar" class="sidebar-toggle">
            <div class="sidebar-logo mt-3">
                <img src="../../assets/images/official_logo.png" width="90px" height="55px">
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
                    <span style="font-size: 18px;">Stock Out</span>
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
                <a href="../../logout.php" class="sidebar-link">
                    <i class="bi bi-box-arrow-right"></i>
                    <span style="font-size: 18px;">Logout</span>
                </a>
            </li>
        </aside>

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <button class="toggler-btn" type="button">
                        <i class="bi bi-list-ul" style="font-size: 28px;"></i>
                    </button>
                </div>
                <div class="logo d-flex align-items-center">
                    <span class="username me-2 fw-bold text-primary">
                        <?= htmlspecialchars($firstname) ?> (Admin)
                    </span>
                </div>
            </div>

            <div style="width:95%; margin:20px auto; background:#f8f9fa; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.08);">
                <div class="card shadow-lg border-0 p-4 rounded-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <h3 class="text-primary fw-bold m-0">Supply Requests Overview</h3>
                        <input type="text" id="searchInput" class="form-control" style="max-width: 300px;" placeholder="🔍 Search request...">
                    </div>
                    <ul class="nav nav-tabs" id="requestTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab" aria-controls="approved" aria-selected="true">
                                Approved
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="declined-tab" data-bs-toggle="tab" data-bs-target="#declined" type="button" role="tab" aria-controls="declined" aria-selected="false">
                                Declined
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="delivered-tab" data-bs-toggle="tab" data-bs-target="#delivered" type="button" role="tab" aria-controls="delivered" aria-selected="false">
                                Delivered
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="ledger-tab" data-bs-toggle="tab" data-bs-target="#ledger" type="button" role="tab" aria-controls="ledger" aria-selected="false">
                                Stock Ledger
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="requestTabsContent">
                        <div class="tab-pane fade show active" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                            <?php include_once 'logics/request_table.php';
                            showRequests($requests, 'Approved'); ?>
                        </div>

                        <div class="tab-pane fade" id="declined" role="tabpanel" aria-labelledby="declined-tab">
                            <?php include_once 'logics/request_table.php';
                            showRequests($requests, 'Cancelled'); ?>
                        </div>
                        <div class="tab-pane fade" id="delivered" role="tabpanel" aria-labelledby="declined-tab">
                            <?php include_once 'logics/request_table.php';
                            showRequests($requests, 'Delivered'); ?>
                        </div>
                        <div class="tab-pane fade" id="ledger" role="tabpanel" aria-labelledby="ledger-tab">
                            <table class="table table-bordered table-hover mt-3">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th>Qty</th>
                                        <th>Date</th>
                                        <th>Department</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($r = $ledger->fetch_assoc()): ?>
                                        <tr class="text-center">
                                            <td class="<?= $r['type'] == 'IN' ? 'text-success' : 'text-danger' ?> fw-bold">
                                                <?= $r['type'] ?>
                                            </td>
                                            <td><?= $r['description'] ?></td>
                                            <td><?= $r['qty'] ?></td>
                                            <td><?= $r['date'] ?></td>
                                            <td><?= $r['remarks'] ?></td>
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

    <script src="../../assets/bootstrap/bootstrap.bundle.js"></script>
    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../../assets/bootstrap/all.min.js"></script>
    <script src="assets/admin.js"></script>
</body>

</html>