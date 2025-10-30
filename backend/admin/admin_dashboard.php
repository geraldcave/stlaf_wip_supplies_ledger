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
</head>

<body>

    <!-- ✅ NAVBAR -->
    <nav class="navbar navbar-expand-lg px-4 shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <img src="../../assets/images/official_logo.png" width="70" height="55" alt="Logo">
                <span class="fw-bold">Admin Dashboard</span>
            </a>
            <div class="ms-auto d-flex align-items-center gap-3">
                <!-- <span class="navbar-text">Welcome, <?= htmlspecialchars($firstname) ?> (Admin)</span> -->
                <a href="../../logout.php"><button class="logout-btn">Logout</button></a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card shadow p-4">
            <h3 class="text-center mb-4">Pending Supply Requests</h3>

            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr class="text-center">
                        <th>#</th>
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
                <tbody>
                    <?php if (!empty($requests)): ?>
                        <?php foreach ($requests as $i => $r): ?>
                            <tr class="text-center">
                                <td><?= $i + 1 ?></td>
                                <td><?= htmlspecialchars($r['name']) ?></td>
                                <td><?= ucfirst($r['department']) ?></td>
                                <td><?= htmlspecialchars($r['item']) ?></td>
                                <td><?= htmlspecialchars($r['size']) ?></td>
                                <td><?= htmlspecialchars($r['quantity']) ?></td>
                                <td><?= htmlspecialchars($r['unit']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $r['status'] === 'Delivered' ? 'success' : ($r['status'] === 'Cancelled' ? 'danger' : 'secondary') ?>">
                                        <?= ucfirst($r['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($r['status'] === 'Pending'): ?>
                                        <button onclick="updateRequestStatus(<?= $r['req_id'] ?>, 'Delivered')" class="btn btn-success btn-sm">Approve</button>
                                        <button onclick="updateRequestStatus(<?= $r['req_id'] ?>, 'Cancelled')" class="btn btn-danger btn-sm">Decline</button>
                                    <?php elseif ($r['status'] === 'Delivered'): ?>
                                        <span class="text-success fw-bold">Delivered</span>
                                    <?php elseif ($r['status'] === 'Cancelled'): ?>
                                        <span class="text-danger fw-bold">Cancelled</span><br>
                                        <small class="badge bg-light text-dark border mt-1">Reason: <?= htmlspecialchars($r['cancel_reason'] ?: 'N/A') ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">No requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
                        <button type="submit" class="btn btn-decline">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../../assets/bootstrap/all.min.js"></script>
    <script>
        function updateRequestStatus(req_id, status) {
            let reason = null;

            if (status === 'Cancelled') {
                reason = prompt("Please enter a reason for cancellation:");
                if (!reason) {
                    alert("Reason is required to cancel.");
                    return;
                }
            }

            fetch('../../auth/oop/request_form.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        method: 'updateStatus',
                        req_id: req_id,
                        status: status,
                        reason: reason
                    })
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === 'success') location.reload();
                })
                .catch(err => console.error('Error:', err));
        }
    </script>

</body>

</html>