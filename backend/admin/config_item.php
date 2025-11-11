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

$countQuery = $search !== ""
    ? $conn->query("SELECT COUNT(*) AS total FROM items WHERE description LIKE '%$search%'")
    : $conn->query("SELECT COUNT(*) AS total FROM items");

$totalItems = $countQuery->fetch_assoc()['total'];
$totalPages = $totalItems > 0 ? ceil($totalItems / $limit) : 1;

$query = "SELECT * FROM items WHERE description LIKE '%$search%' ORDER BY description ASC LIMIT $limit OFFSET $offset";
$items = $conn->query($query);

$firstname = ucfirst($_SESSION['username'] ?? 'Admin');

$unitOptions = ['PC', 'BOTTLE', 'BOX', 'REAM', 'ROLL', 'PACK'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updateField'])) {
    $id = (int) $_POST['id'];
    $field = $_POST['field'];
    $value = $_POST['value'];
    if (in_array($field, ['description', 'unit', 'qty_on_hand'])) {
        $conn->query("UPDATE items SET $field = '" . $conn->real_escape_string($value) . "' WHERE id = $id");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
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
                <a href="admin_dashboard.php" class="sidebar-link"><i class="bi bi-cast">
                    </i> <span>Dashboard</span></a>
            </li>
            <li class="sidebar-item">
                <a href="req_tab.php" class="sidebar-link active"><i class="bi bi-box"></i> <span>Employee Requests</span></a>
            </li>
            <li class="sidebar-item"><a href="ins_form.php" class="sidebar-link active"><i class="bi bi-basket">
                    </i> <span>Ins Forms</span></a>
            </li>
            <li class="sidebar-item">
                <a href="stock_in.php" class="sidebar-link active"><i class="bi bi-basket">
                    </i> <span>Stock In</span></a>
            </li>
            <li class="sidebar-item">
                <a href="stock_out.php" class="sidebar-link active"><i class="bi bi-basket"></i>
                    <span>Stock Out</span></a>
            </li>
            <li class="sidebar-item">
                <a href="inventory_dashboard.php" class="sidebar-link active"><i class="bi bi-speedometer2"></i>
                    <span>Supply Tracking</span></a>
            </li>
            <li class="sidebar-item">
                <a href="config_item.php" class="sidebar-link active"><i class="bi bi-gear"></i>
                    <span>Configuration</span></a>
            </li>
            <li class="sidebar-item">
                <a href="../../logout.php" class="sidebar-link"><i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span></a>
            </li>
        </aside>

        <div class="main">
            <div class="topbar">
                <div class="toggle">
                    <button class="toggler-btn"><i class="bi bi-list-ul" style="font-size:28px;"></i></button>
                </div>
                <div class="logo d-flex align-items-center">
                    <span class="username me-2 fw-bold text-primary"><?= htmlspecialchars($firstname) ?> (Admin)</span>
                </div>
            </div>

            <div style="width:95%; margin:20px auto; background:#f8f9fa; border-radius:10px; box-shadow:0 2px 6px rgba(0,0,0,0.08);">
                <div class="card shadow-lg border-0 p-4 rounded-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="text-primary fw-bold m-0">📦 Supply Tracker</h3>
                        <input type="text" id="searchInput" class="form-control" style="max-width:280px;"
                            placeholder="🔍 Search item..." value="<?= htmlspecialchars($search) ?>" onkeyup="if(event.keyCode==13) applySearch();">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-center">Remaining Stock</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="inventoryTable">
                                <?php while ($row = $items->fetch_assoc()):
                                    $onhand = $row['qty_on_hand'];
                                    $threshold = $row['threshold'] ?? 10;
                                ?>
                                    <tr class="<?= ($onhand < $threshold) ? 'table-danger' : ''; ?>">
                                        <td><input type="text" class="editable-input" data-id="<?= $row['id'] ?>" data-field="description" value="<?= htmlspecialchars($row['description']) ?>"></td>
                                        <td class="text-center">
                                            <select class="editable-select" data-id="<?= $row['id'] ?>" data-field="unit">
                                                <?php foreach ($unitOptions as $u): ?>
                                                    <option value="<?= $u ?>" <?= $u == $row['unit'] ? 'selected' : '' ?>><?= $u ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </td>
                                        <td class="text-center"><input type="number" class="editable-input" data-id="<?= $row['id'] ?>" data-field="qty_on_hand" value="<?= $onhand ?>" min="0"></td>
                                        <td class="text-center"><span class="badge <?= ($onhand < $threshold) ? 'bg-danger' : 'bg-success' ?>"><?= ($onhand < $threshold) ? 'LOW STOCK' : 'OK' ?></span></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <nav aria-label="Page navigation" class="mt-3">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next</a>
                            </li>
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
                        body: "updateField=1&id=" + id + "&field=" + field + "&value=" + encodeURIComponent(value)
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && field === 'qty_on_hand') {
                            const row = this.closest("tr");
                            const badge = row.querySelector(".badge");
                            if (value < 10) {
                                row.classList.add("table-danger");
                                badge.className = "badge bg-danger";
                                badge.textContent = "LOW STOCK";
                            } else {
                                row.classList.remove("table-danger");
                                badge.className = "badge bg-success";
                                badge.textContent = "OK";
                            }
                        }
                    });
            });
        });
    </script>
</body>

</html>