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
    <title>STLAF | Admin Requests</title>
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../../assets/images/sub_logo_light.png">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/super.css">
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
                <a href="dashb.php" class="sidebar-link active">
                    <i class="bi bi-box"></i>
                    <span style="font-size: 18px;">Pending Requests</span>
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
                        <h3 class="text-primary fw-bold">Requests Overview</h3>
                        <span class="text-muted small">Total: <?= count($requests) ?> requests</span>
                    </div>

                    <div class="table-container p-4 shadow-sm rounded-4 bg-white">
                        <div class="table-responsive elegant-table" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="text-center">
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Item</th>
                                        <th>Size</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="requestTableBody">
                                    <?php if (!empty($requests)): ?>
                                        <?php foreach ($requests as $r): ?>
                                            <tr class="text-center">
                                                <td><span class="fw-bold text-primary"><?= htmlspecialchars($r['req_id']) ?></span></td>
                                                <td><?= htmlspecialchars($r['name']) ?></td>
                                                <td><?= ucfirst($r['department']) ?></td>
                                                <td><?= htmlspecialchars($r['item']) ?></td>
                                                <td><?= htmlspecialchars($r['size']) ?></td>
                                                <td><?= htmlspecialchars($r['quantity']) ?></td>
                                                <td><?= htmlspecialchars($r['unit']) ?></td>
                                                <td>
                                                    <span class="badge bg-<?= $r['status'] === 'Delivered' ? 'success' : ($r['status'] === 'Cancelled' ? 'danger' : 'secondary') ?> px-3 py-2 shadow-sm">
                                                        <?= ucfirst($r['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($r['status'] === 'Pending'): ?>
                                                        <button onclick="updateRequestStatus(<?= $r['req_id'] ?>, 'Delivered')" class="btn btn-success btn-sm px-3 shadow-sm">Approve</button>
                                                        <button onclick="updateRequestStatus(<?= $r['req_id'] ?>, 'Cancelled')" class="btn btn-danger btn-sm px-3 shadow-sm">Decline</button>
                                                    <?php elseif ($r['status'] === 'Delivered'): ?>
                                                        <span class="text-success fw-bold">Delivered</span>
                                                    <?php elseif ($r['status'] === 'Cancelled'): ?>
                                                        <span class="text-danger fw-bold">Cancelled</span><br>
                                                        <div class="cancel-box mt-2">
                                                            <small><strong>Reason:</strong> <?= htmlspecialchars($r['cancel_reason'] ?: 'N/A') ?></small>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-3">No requests found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <nav>
                        <ul class="pagination justify-content-center mt-3" id="pagination"></ul>
                    </nav>
                </div>
            </div>
            <!-- ===== PAGE CONTENT END ===== -->
        </div>
    </div>

    <!-- Decline Reason Modal -->
    <div class="modal fade" id="declineModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-dark">
                <div class="modal-header">
                    <h5 class="modal-title">Decline Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="declineForm">
                    <div class="modal-body">
                        <input type="hidden" name="request_id" id="declineRequestId">
                        <label class="form-label">Reason for Decline:</label>
                        <textarea name="reason" class="form-control" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../../assets/bootstrap/bootstrap.bundle.js"></script>
    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../../assets/bootstrap/all.min.js"></script>
    <script src="assets/admin.js"></script>
</body>


</html>