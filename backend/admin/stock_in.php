<?php
session_start();
require_once '../../sql/config.php';
require_once 'logics/stock_in.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$stock = new StockIn($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $saved = $stock->addStockIn($_POST['item_id'], $_POST['qty_in'], $_POST['remarks']);
}

$items = $stock->getItems();
$firstname = ucfirst($_SESSION['username'] ?? 'Admin');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | StockIn Tab</title>
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
            <div class="container mt-4">

                <?php if (isset($saved) && $saved): ?>
                    <div class="alert alert-success">✅ Stock Updated Successfully</div>
                <?php elseif (isset($saved) && !$saved): ?>
                    <div class="alert alert-danger">❌ Failed to Update Stock</div>
                <?php endif; ?>

                <div class="card shadow p-4 border-0">
                    <h4 class="fw-bold">Stock In Form</h4>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Select Item</label>
                            <select name="item_id" class="form-select" required>
                                <option value="">-- Select Item --</option>
                                <?php while ($row = $items->fetch_assoc()): ?>
                                    <option value="<?= $row['id'] ?>">
                                        <?= $row['description'] ?> (On Hand: <?= $row['qty_on_hand'] ?> <?= $row['unit'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Quantity to Add</label>
                            <input type="number" name="qty_in" min="1" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Department</label>
                            <input type="text" name="remarks" class="form-control">
                        </div>

                        <button class="btn btn-primary w-100 fw-bold">Save Stock In</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../../assets/bootstrap/all.min.js"></script>
    <script>
        const toggler = document.querySelector(".toggler-btn");
        toggler.addEventListener("click", function() {
            document.querySelector("#sidebar").classList.toggle("collapsed");
        });
    </script>

</body>

</html>