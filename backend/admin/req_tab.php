<?php
session_start();
require_once '../../sql/config.php';
require_once "../../auth/oop/request_form.php";

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$request = new Request($conn);

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
                <a href="archived_items.php" class="sidebar-link active"><i class="bi bi-archive"></i>
                    <span>Archived Items</span></a>
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
                <div class="card shadow-lg border-0 p-4 rounded-4" style="height: 83vh;">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                        <h3 class="fw-bold m-0">Pending Requests</h3>
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
                                                        <button onclick="updateRequestStatus(<?= $r['req_id'] ?>, 'Approved')" class="btn btn-success btn-sm px-2 shadow-sm">Approve</button>

                                                        <button onclick="updateRequestStatus(<?= $r['req_id'] ?>, 'Cancelled')" class="btn btn-danger btn-sm px-2 shadow-sm">Decline</button>

                                                    <?php elseif (strtolower($r['status']) === 'approved'): ?>
                                                        <button onclick="updateRequestStatus(<?= $r['req_id'] ?>, 'Delivered')" class="btn btn-primary btn-sm px-2 shadow-sm">Delivered</button>
                                                    <?php endif; ?>

                                                    <button onclick="deleteRequest(<?= $r['req_id'] ?>)" class="btn btn-outline-danger btn-sm px-2 shadow-sm">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
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
        // live refresh of pending requests
        function loadPendingRequests() {
            fetch('get_pending_requests.php')
                .then(response => {
                    if (!response.ok) {
                        console.error('HTTP error', response.status);
                    }
                    return response.text();
                })
                .then(html => {
                    const tableBody = document.getElementById('requestTableBody');
                    if (!tableBody) return;

                    const searchInput = document.getElementById('searchInput');
                    const filter = searchInput ? searchInput.value.toLowerCase().trim() : '';

                    tableBody.innerHTML = html;

                    // re-apply filter after refresh
                    if (filter && searchInput) {
                        const rows = Array.from(tableBody.querySelectorAll("tr"));
                        let visibleCount = 0;

                        rows.forEach((row) => {
                            if (row.classList.contains("no-result-row") || row.textContent.includes("No requests available.")) {
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
                    }
                })
                .catch(err => console.error('Fetch error:', err));
        }

        // refresh every 3 seconds
        setInterval(loadPendingRequests, 3000);

        // existing search code
        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById("searchInput");
            const tableBody = document.getElementById("requestTableBody");

            if (!searchInput || !tableBody) return;

            searchInput.addEventListener("keyup", function() {
                const filter = searchInput.value.toLowerCase().trim();
                const rows = Array.from(tableBody.querySelectorAll("tr"));
                let visibleCount = 0;

                rows.forEach((row) => {
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

        function deleteRequest(reqId) {
            if (!confirm("Are you sure you want to permanently delete this request?")) return;

            fetch("delete_request.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "request_id=" + reqId
                })
                .then(res => res.text())
                .then(response => {
                    console.log(response); // ✅ DEBUG LINE
                    alert( response);

                    if (response.trim() === "success") {
                        loadPendingRequests();
                    }
                })
                .catch(err => console.error("Delete error:", err));
        }
    </script>

</body>

</html>