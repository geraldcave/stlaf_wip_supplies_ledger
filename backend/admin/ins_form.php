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
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    for ($i = 0; $i < count($_POST['description']); $i++) {
        $item->addItem(
            $_POST['description'][$i],
            $_POST['unit'][$i],
            $_POST['unit_price'][$i],
            $_POST['supplier'][$i],
            $_POST['department'][$i],
            $_POST['threshold'][$i]
        );
    }

    $saved = true; // mark success
}


$firstname = ucfirst($_SESSION['username'] ?? 'Admin');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | Ins Form Tab</title>
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
                <a href="config_item.php" class="sidebar-link active"><i class="bi bi-gear"></i>
                    <span>Configuration</span></a>
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
                    <div class="alert alert-success fw-bold">✅ Item Added Successfully!</div>
                <?php elseif (isset($saved) && !$saved): ?>
                    <div class="alert alert-danger fw-bold">❌ Failed to Save Item.</div>
                <?php endif; ?>

                <div class="card shadow p-4 border-0" style="background:#f7f9fc;">
                    <h4 class="mb-3 fw-bold" style="color:#123765;">Add New Supply Item</h4>
                    <form method="POST">
                        <div id="itemRows">

                            <div class="row item-row border rounded p-3 mb-3">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Item Description</label>
                                    <input type="text" name="description[]" class="form-control" required>
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Unit</label>
                                    <select name="unit[]" class="form-select" required>
                                 <option value="BOTTLE">BOTTLE</option>       <option value="PC">PC</option>
                                        <option value="REAM">REAM</option>
                                        <option value="PACK">PACK</option>
                                        <option value="BOX">BOX</option>
                                        
                                        <option value="ROLL">ROLL</option>
                                    </select>
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Unit Price</label>
                                    <input type="number" step="0.01" name="unit_price[]" class="form-control">
                                </div>

                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Supplier</label>
                                    <input type="text" name="supplier[]" class="form-control">
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Department</label>
                                    <select name="department[]" class="form-select">
                                        <option value="ALL">ALL</option>
                                        <option value="HR">HR</option>
                                        <option value="ACCOUNTING">ACCOUNTING</option>
                                        <option value="CORPORATE">CORPORATE</option>
                                        <option value="MARKETING">MARKETING</option>
                                        <option value="LITIGATION">LITIGATION</option>
                                        <option value="I.T">I.T</option>
                                    </select>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label fw-bold">Threshold</label>
                                    <input type="number" name="threshold[]" value="0" class="form-control">
                                </div>

                                <div class="col-md-1 d-flex align-items-end mb-2">
                                    <button type="button" class="btn btn-danger remove-row w-100">
                                        <i class="bi bi-dash-circle"></i>
                                    </button>
                                </div>
                            </div>

                        </div>

                        <button type="button" id="addRow" class="btn btn-secondary fw-bold mb-3">
                            <i class="bi bi-plus-circle"></i> Add Another Item
                        </button>

                        <button class="btn w-100 text-white fw-bold" style="background:#123765;">
                            Save All Items
                        </button>
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
        document.getElementById("addRow").addEventListener("click", function() {
            const row = document.querySelector(".item-row").cloneNode(true);

            // Clear input values in cloned row
            row.querySelectorAll("input").forEach(input => input.value = "");

            document.getElementById("itemRows").appendChild(row);
        });

        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("remove-row") && document.querySelectorAll(".item-row").length > 1) {
                e.target.closest(".item-row").remove();
            }
        });
    </script>

</body>

</html>