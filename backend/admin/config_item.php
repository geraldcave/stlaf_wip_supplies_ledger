<?php
session_start();
require_once '../../sql/config.php';
require_once 'logics/items.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$item = new Item($conn);

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$limit = isset($_GET['limit']) && $_GET['limit'] > 0 ? (int) $_GET['limit'] : 10;
$page  = isset($_GET['page']) && $_GET['page'] > 0 ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['updateField'])) {
        $id = (int) $_POST['id'];
        $field = $_POST['field'];
        $value = $_POST['value'];

        if (in_array($field, ['description', 'unit', 'qty_on_hand', 'threshold'])) {
            $stmt = $conn->prepare("UPDATE items SET $field = ? WHERE id = ?");
            $stmt->bind_param("si", $value, $id);
            $ok = $stmt->execute();
            echo json_encode(['success' => (bool) $ok]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    if (isset($_POST['action']) && isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        $action = $_POST['action'];

        if ($action === 'archive') {
            $ok = $item->archiveItem($id);
            echo json_encode(['success' => (bool) $ok, 'type' => 'archive']);
            exit;
        }

        if ($action === 'delete') {
            $ok = $item->deleteItem($id);
            echo json_encode(['success' => (bool) $ok, 'type' => 'delete']);
            exit;
        }

        echo json_encode(['success' => false]);
        exit;
    }
}

$countQuery = $search !== ""
    ? $conn->query("SELECT COUNT(*) AS total FROM items WHERE is_archived = 0 AND description LIKE '%$search%'")
    : $conn->query("SELECT COUNT(*) AS total FROM items WHERE is_archived = 0");

$totalItems = $countQuery->fetch_assoc()['total'];
$totalPages = $totalItems > 0 ? ceil($totalItems / $limit) : 1;

$query = "SELECT * FROM items 
          WHERE is_archived = 0 AND description LIKE '%$search%' 
          ORDER BY description ASC 
          LIMIT $limit OFFSET $offset";
$items = $conn->query($query);

$firstname = ucfirst($_SESSION['username'] ?? 'Admin');
$unitOptions = ['PC', 'BOTTLE', 'BOX', 'REAM', 'ROLL', 'PACK'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | Supply Tracking</title>
    <link rel="icon" type="image/png" href="../../assets/images/sub_logo_light.png">
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/super.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .editable-input {
            width: 100%;
            text-align: center;
            border: none;
            background: transparent;
        }

        .editable-input:focus {
            outline: 1px solid #0d6efd;
            background: #fff;
        }

        select.editable-select {
            width: 100%;
            text-align: center;
            border: none;
            background: transparent;
        }

        select.editable-select:focus {
            outline: 1px solid #0d6efd;
            background: #fff;
        }
    </style>
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
                <div class="toggle"><button class="toggler-btn"><i class="bi bi-list-ul" style="font-size:28px;"></i></button></div>
                <div class="logo d-flex align-items-center"><span class="username me-2 fw-bold text-primary"><?= htmlspecialchars($firstname) ?> (Admin)</span></div>
            </div>

            <div style="width:95%; margin:20px auto; background:#f8f9fa; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.08);">
                <div class="card shadow-lg border-0 p-4 rounded-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="fw-bold m-0">Configuration</h3>
                        <input type="text" id="searchInput" class="form-control" style="max-width:280px;" placeholder="🔍 Search item..." value="<?= htmlspecialchars($search) ?>" onkeyup="if(event.keyCode==13) applySearch();">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Unit</th>
                                    <th class="text-end">Stock</th>
                                    <th class="text-end">Threshold</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryTable">
                                <?php while ($row = $items->fetch_assoc()):
                                    $onhand = $row['qty_on_hand'];
                                    $threshold = $row['threshold'] ?? 10;
                                ?>
                                    <tr data-threshold="<?= $threshold ?>" class="<?= ($onhand < $threshold) ? 'table-danger' : ''; ?>">
                                        <td><input type="text" class="editable-input text-start" data-id="<?= $row['id'] ?>" data-field="description" value="<?= htmlspecialchars($row['description']) ?>"></td>
                                        <td class="text-end">
                                            <select class="editable-select text-end" data-id="<?= $row['id'] ?>" data-field="unit">
                                                <?php foreach ($unitOptions as $u): ?>
                                                    <option value="<?= $u ?>" <?= $u == $row['unit'] ? 'selected' : '' ?>><?= $u ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="text-end"><input type="number" class="editable-input text-end" data-id="<?= $row['id'] ?>" data-field="qty_on_hand" value="<?= $onhand ?>" min="0"></td>
                                        <td class="text-end"><input type="number" class="editable-input text-end" data-id="<?= $row['id'] ?>" data-field="threshold" value="<?= $threshold ?>" min="0"></td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-warning me-1 btn-archive" data-id="<?= $row['id'] ?>"><i class="bi bi-archive"></i></button>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete" data-id="<?= $row['id'] ?>"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <nav aria-label="Page navigation" class="mt-3">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>"><a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">Previous</a></li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>"><a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a></li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>"><a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next</a></li>
                        </ul>
                    </nav>

                    <div class="alert alert-warning mt-3 fw-bold text-center">
                        ⚠️ Items highlighted in <span class="text-danger">RED</span> are below safe stock level.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("searchInput").addEventListener("keyup", function() {
            let value = this.value.toLowerCase();
            document.querySelectorAll("#inventoryTable tr").forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
            });
        });

        function applySearch() {
            let val = document.getElementById("searchInput").value;
            window.location.href = "?search=" + encodeURIComponent(val) + "&page=1";
        }

        document.querySelectorAll(".editable-input, .editable-select").forEach(input => {
            input.addEventListener("change", function() {
                const id = this.dataset.id;
                const field = this.dataset.field;
                const value = this.value;

                fetch("", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "updateField=1&id=" + encodeURIComponent(id) + "&field=" + encodeURIComponent(field) + "&value=" + encodeURIComponent(value)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const row = this.closest("tr");
                            let threshold = parseInt(row.getAttribute("data-threshold") || "10");
                            let qty = parseInt(row.querySelector("[data-field='qty_on_hand']").value);

                            if (field === 'threshold') {
                                row.setAttribute("data-threshold", value);
                                threshold = parseInt(value);
                            }

                            if (qty < threshold) {
                                row.classList.add("table-danger");
                            } else {
                                row.classList.remove("table-danger");
                            }
                        }
                    });
            });
        });

        document.querySelectorAll(".btn-archive").forEach(btn => {
            btn.addEventListener("click", function() {
                const id = this.dataset.id;
                if (!confirm("Archive this item? It will be hidden from the active list.")) return;

                fetch("", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "action=archive&id=" + encodeURIComponent(id)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.closest("tr").remove();
                        } else {
                            alert("Failed to archive item.");
                        }
                    });
            });
        });

        document.querySelectorAll(".btn-delete").forEach(btn => {
            btn.addEventListener("click", function() {
                const id = this.dataset.id;
                if (!confirm("Are you sure you want to permanently delete this item?")) return;

                fetch("", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded"
                        },
                        body: "action=delete&id=" + encodeURIComponent(id)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.closest("tr").remove();
                        } else {
                            alert("Failed to delete item.");
                        }
                    });
            });
        });

        document.querySelector(".toggler-btn").addEventListener("click", function() {
            document.querySelector("#sidebar").classList.toggle("collapsed");
        });
    </script>
</body>

</html>