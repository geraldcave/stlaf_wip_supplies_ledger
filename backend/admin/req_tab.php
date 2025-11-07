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
    } elseif ($id && $status === 'Cancelled' && $reason) {
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
    <title>STLAF | Request Tab</title>
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
                        <h3 class="text-primary fw-bold m-0">Pending Requests</h3>
                        <input type="text" id="searchInput" class="form-control" style="max-width: 300px;" placeholder="🔍 Search request...">
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
                                    <?php
                                    $hasPending = false;
                                    if (!empty($requests)):
                                        foreach ($requests as $r):
                                            if (in_array(strtolower($r['status']), ['cancelled', 'delivered'])) continue;
                                            $hasPending = true;
                                    ?>
                                            <tr class="text-center">
                                                <td><span class="fw-bold text-primary"><?= htmlspecialchars($r['req_id']) ?></span></td>
                                                <td><?= htmlspecialchars($r['name']) ?></td>
                                                <td><?= ucfirst($r['department']) ?></td>
                                                <td><?= htmlspecialchars($r['item']) ?></td>
                                                <td><?= htmlspecialchars($r['size']) ?></td>
                                                <td><?= htmlspecialchars($r['quantity']) ?></td>
                                                <td><?= htmlspecialchars($r['unit']) ?></td>

                                                <td>
                                                    <?php if (strtolower($r['status']) === 'pending'): ?>
                                                        <span class="badge bg-secondary px-3 py-2 shadow-sm">Pending</span>

                                                    <?php elseif (strtolower($r['status']) === 'approved'): ?>
                                                        <span class="badge bg-success px-3 py-2 shadow-sm">Approved</span>
                                                    <?php endif; ?>
                                                </td>

                                                <td>
                                                    <?php if (strtolower($r['status']) === 'pending'): ?>
                                                        <button onclick="updateRequestStatus(<?= $r['req_id'] ?>, 'Approved')" class="btn btn-success btn-sm px-3 shadow-sm">Approve</button>
                                                        <button onclick="updateRequestStatus(<?= $r['req_id'] ?>, 'Cancelled')" class="btn btn-danger btn-sm px-3 shadow-sm">Decline</button>

                                                    <?php elseif (strtolower($r['status']) === 'approved'): ?>
                                                        <button onclick="updateRequestStatus(<?= $r['req_id'] ?>, 'Delivered')" class="btn btn-primary btn-sm px-3 shadow-sm">Delivered</button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php
                                        endforeach;
                                    endif;
                                    if (!$hasPending): ?>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted py-3">No requests available.</td>
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
        </div>
    </div>

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

    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../../assets/bootstrap/all.min.js"></script>
    <script src="assets/admin.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById("searchInput");
            const tableBody = document.getElementById("requestTableBody");

            if (!searchInput || !tableBody) return;

            searchInput.addEventListener("keyup", function() {
                const filter = searchInput.value.toLowerCase().trim();
                const rows = Array.from(tableBody.querySelectorAll("tr"));
                let visibleCount = 0;

                rows.forEach((row) => {
                    // Skip the "No pending requests" or "no results" row
                    if (row.classList.contains("no-result-row") || row.textContent.includes("No pending requests")) {
                        row.style.display = "none";
                        return;
                    }

                    const text = row.textContent.toLowerCase();
                    if (text.includes(filter)) {
                        row.style.display = "";
                        visibleCount++;
                    } else {
                        row.style.display = "none";
                    }
                });

                // Handle no result message
                let noResultRow = document.getElementById("noResultRow");
                if (visibleCount === 0) {
                    if (!noResultRow) {
                        noResultRow = document.createElement("tr");
                        noResultRow.id = "noResultRow";
                        noResultRow.classList.add("no-result-row");
                        noResultRow.innerHTML = `<td colspan="9" class="text-center text-muted py-3">No matching results.</td>`;
                        tableBody.appendChild(noResultRow);
                    }
                } else if (noResultRow) {
                    noResultRow.remove();
                }
            });
        });
    </script>

</body>

</html>