<?php
session_start();
require_once '../../sql/config.php';
require_once "../../auth/oop/request_form.php";

// 🔒 Access control — only admin can view
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

// 🧾 Handle POST actions (if any)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['req_id'] ?? $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null;
    $reason = $_POST['reason'] ?? null;

    if ($id && $action === 'approve') {
        $request->updateStatus($id, 'Delivered');
    } elseif ($id && $reason) {
        $request->updateStatus($id, 'Cancelled', $reason);
    }
}

// 🗂 Get all requests
$requests = $request->getAllRequests();
$firstname = ucfirst($_SESSION['username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | Admin Requests</title>
    <link rel="icon" type="image/png" href="../../assets/images/sub_logo_light.png">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/super.css">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body>
    <div class="d-flex">
        <!-- ===== Sidebar ===== -->
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
                <a href="#" class="sidebar-link active">
                    <i class="bi bi-box"></i>
                    <span style="font-size: 18px;">Supply Requests</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="../../logout.php" class="sidebar-link">
                    <i class="bi bi-box-arrow-right"></i>
                    <span style="font-size: 18px;">Logout</span>
                </a>
            </li>
        </aside>

        <!-- ===== Main Content ===== -->
        <div class="main">
            <!-- ===== Topbar ===== -->
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
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item text-danger" href="../../logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- ===== PAGE CONTENT START ===== -->
            <div style="width:95%; margin:20px auto; background:#f8f9fa; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.08);">
                <div class="card shadow-lg border-0 p-4 rounded-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="text-primary fw-bold">Supply Requests Overview</h3>
                        <span class="text-muted small">Total: <?= count($requests) ?> requests</span>
                    </div>

                    <!-- ==== Tabs ==== -->
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
                    </ul>

                    <div class="tab-content mt-3" id="requestTabsContent">
                        <!-- === Approved === -->
                        <div class="tab-pane fade show active" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                            <?php include 'logics/request_table.php';
                            showRequests($requests, 'Delivered'); ?>
                        </div>

                        <!-- === Declined === -->
                        <div class="tab-pane fade" id="declined" role="tabpanel" aria-labelledby="declined-tab">
                            <?php include 'logics/request_table.php';
                            showRequests($requests, 'Cancelled'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ===== PAGE CONTENT END ===== -->
        </div>
    </div>

    <!-- ===== Scripts ===== -->
    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="assets/admin.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const lastTab = localStorage.getItem("activeTab");
            if (lastTab) {
                const triggerEl = document.querySelector(`[data-bs-target="${lastTab}"]`);
                const tab = new bootstrap.Tab(triggerEl);
                tab.show();
            }

            document.querySelectorAll('#requestTabs button[data-bs-toggle="tab"]').forEach(button => {
                button.addEventListener('shown.bs.tab', e => {
                    localStorage.setItem("activeTab", e.target.getAttribute("data-bs-target"));
                });
            });
        });
    </script>
</body>

</html>