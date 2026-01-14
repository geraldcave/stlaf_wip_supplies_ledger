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

// --- 1. HANDLE AJAX REQUESTS (Auto-Save & Delete) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // A. Handle Inline Edit (Auto-Save)
    if (isset($_POST['action']) && $_POST['action'] === 'update_field') {
        $id = (int) $_POST['id'];
        $field = $_POST['field'];
        $value = $_POST['value'];

        // Allowed columns to prevent SQL injection
        $allowed = ['description', 'supplier', 'created_at'];
        
        if (in_array($field, $allowed)) {
            $stmt = $conn->prepare("UPDATE items SET $field = ? WHERE id = ?");
            $stmt->bind_param("si", $value, $id);
            $success = $stmt->execute();
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid field']);
        }
        exit;
    }

    // B. Handle Delete
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = (int) $_POST['id'];
        $ok = $item->deleteItem($id);
        echo json_encode(['success' => (bool) $ok]);
        exit;
    }
}

// --- 2. FETCH DATA ---
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : "";
$limit = 10;
$page  = isset($_GET['page']) && $_GET['page'] > 0 ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Count Total
$countQuery = $search !== ""
    ? $conn->query("SELECT COUNT(*) AS total FROM items WHERE is_archived = 0 AND description LIKE '%$search%'")
    : $conn->query("SELECT COUNT(*) AS total FROM items WHERE is_archived = 0");
$totalItems = $countQuery->fetch_assoc()['total'];
$totalPages = $totalItems > 0 ? ceil($totalItems / $limit) : 1;

// Fetch Items
$query = "SELECT * FROM items 
          WHERE is_archived = 0 AND description LIKE '%$search%' 
          ORDER BY description ASC 
          LIMIT $limit OFFSET $offset";
$items = $conn->query($query);

$firstname = ucfirst($_SESSION['username'] ?? 'Admin');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | Configuration</title>
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/style.css">
        <link rel="stylesheet" href="assets/super.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .editable-input {
            width: 100%;
            border: 1px solid transparent;
            background: transparent;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .editable-input:hover {
            border: 1px solid #ced4da;
            background: #fff;
        }
        .editable-input:focus {
            outline: none;
            border: 1px solid #0d6efd;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
        }
        input[type="date"].editable-input {
            text-align: center;
            cursor: pointer;
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
                <a href="supply.php" class="sidebar-link active"><i class="bi bi-gear"></i>
                    <span>Update Supplier & Date</span></a>
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

            <div style="width:95%; margin:20px auto;">
                
                <div id="saveToast" class="position-fixed top-0 end-0 p-3" style="z-index: 1050; display:none;">
                    <div class="toast show align-items-center text-white bg-success border-0" role="alert">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-check-circle-fill me-2"></i> Saved!
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-lg border-0 p-4 rounded-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="fw-bold m-0" style="color:#123765">Item Configuration</h3>
                        <form method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control me-2" style="max-width:280px;" placeholder="🔍 Search item..." value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="btn btn-primary">Search</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr class="fw-bold text-center">
                                    <th style="width: 40%">Description</th>
                                    <th style="width: 25%">Supplier</th>
                                    <th style="width: 25%">Date Added</th>
                                    <th style="width: 10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $items->fetch_assoc()): 
                                    // Format Date for Input (YYYY-MM-DD)
                                    $dateVal = isset($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : '';
                                ?>
                                    <tr>
                                        <td>
                                            <input type="text" class="editable-input fw-bold" 
                                                   data-id="<?= $row['id'] ?>" 
                                                   data-field="description" 
                                                   value="<?= htmlspecialchars($row['description']) ?>">
                                        </td>

                                        <td>
                                            <input type="text" class="editable-input text-center" 
                                                   data-id="<?= $row['id'] ?>" 
                                                   data-field="supplier" 
                                                   value="<?= htmlspecialchars($row['supplier']) ?>">
                                        </td>

                                        <td>
                                            <input type="date" class="editable-input text-center" 
                                                   data-id="<?= $row['id'] ?>" 
                                                   data-field="created_at" 
                                                   value="<?= $dateVal ?>">
                                        </td>
                                        
                                        <td class="text-center">
                                            <button class="btn btn-danger btn-sm btn-delete" data-id="<?= $row['id'] ?>" title="Delete Item">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if($totalPages > 1): ?>
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
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Sidebar
        document.querySelector(".toggler-btn").addEventListener("click", function() {
            document.querySelector("#sidebar").classList.toggle("collapsed");
        });

        // --- 1. AUTO-SAVE FUNCTION ---
        // Select all inputs with class 'editable-input'
        const inputs = document.querySelectorAll(".editable-input");
        const toast = document.getElementById("saveToast");

        inputs.forEach(input => {
            // Listen for 'change' event (triggers when you click away or press enter)
            input.addEventListener("change", function() {
                const id = this.dataset.id;
                const field = this.dataset.field;
                const value = this.value;

                // Send AJAX Request
                fetch("", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `action=update_field&id=${encodeURIComponent(id)}&field=${encodeURIComponent(field)}&value=${encodeURIComponent(value)}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Show Success Toast
                        toast.style.display = "block";
                        setTimeout(() => { toast.style.display = "none"; }, 2000);
                        
                        // Optional: Add visual cue like green border
                        this.style.borderColor = "#198754"; 
                        setTimeout(() => { this.style.borderColor = "transparent"; }, 1500);
                    } else {
                        alert("❌ Failed to save change!");
                        this.style.borderColor = "red";
                    }
                })
                .catch(err => console.error(err));
            });
        });

        // --- 2. DELETE FUNCTION ---
        document.querySelectorAll(".btn-delete").forEach(btn => {
            btn.addEventListener("click", function() {
                const id = this.dataset.id;
                if (!confirm("Are you sure you want to permanently delete this item?")) return;

                fetch("", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
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
    </script>
</body>
</html>