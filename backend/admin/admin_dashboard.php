<?php
session_start();
require_once '../../sql/config.php';
require_once "../../auth/oop/request_form.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['req_id'] ?? $_POST['request_id'] ?? null;
    $action = $_POST['action'] ?? null;
    $reason = $_POST['reason'] ?? null;

    if ($id && $action === 'approve') {
        $request->updateStatus($id, 'Approved');
    } elseif ($id && $action === 'deliver') {
        $request->updateStatus($id, 'Delivered');
    } elseif ($id && $reason) {
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
        <?php include "includes/sidebar.php"; ?>

        <div class="main">
            <?php include "includes/topbar.php"; ?>

            <div style="width:95%; margin:20px auto; background:#f8f9fa; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.08);">
                <div class="card shadow-lg border-0 p-4 rounded-4" style="height: 83vh;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <h3 class="fw-bold m-0">Supply Requests Overview</h3>
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
                        <div class="tab-pane fade" id="delivered" role="tabpanel" aria-labelledby="delivered-tab">
                            <?php include_once 'logics/request_table.php';
                            showRequests($requests, 'Delivered'); ?>
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