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

    $saved = true;

    for ($i = 0; $i < count($_POST['item_id']); $i++) {

        $result = $stock->addStockIn(
            $_POST['item_id'][$i],
            $_POST['qty_in'][$i],
            $_POST['remarks'][$i]
        );

        if (!$result) {
            $saved = false;
        }
    }

    $_SESSION['stock_saved'] = $saved;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Retrieve saved status from session after redirect
if (isset($_SESSION['stock_saved'])) {
    $saved = $_SESSION['stock_saved'];
    unset($_SESSION['stock_saved']);
}

$items = $stock->getItems();
$firstname = ucfirst($_SESSION['username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | Stock In</title>
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../../assets/images/sub_logo_light.png">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/super.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
                <a href="summary.php" class="sidebar-link active"><i class="bi bi-clipboard-data"></i>
                    <span>Summary</span></a>
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
                    <div class="alert alert-success fw-bold">✅ Stock Updated Successfully!</div>
                <?php elseif (isset($saved) && !$saved): ?>
                    <div class="alert alert-danger fw-bold">❌ Failed to Save Some Items.</div>
                <?php endif; ?>

                <div class="card shadow p-4 border-0">
                    <h4 class="fw-bold" style="color: #123765;">Stock In Form</h4>

                    <form method="POST">

                        <div id="stockRows">

                            <div class="row stock-row border rounded p-3 mb-3">

                                <div class="col-md-6 mb-2">
                                    <label class="form-label fw-bold">Select Item</label>
                                    <select name="item_id[]" class="form-select select2-item" required>
                                        <option value="">-- Select Item --</option>
                                        <?php
                                        $items->data_seek(0);
                                        while ($row = $items->fetch_assoc()):
                                        ?>
                                            <option value="<?= $row['id'] ?>">
                                                <?= $row['description'] ?> (On Hand: <?= $row['qty_on_hand'] ?> <?= $row['unit'] ?>)
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="col-md-3 mb-2">
                                    <label class="form-label fw-bold">Quantity</label>
                                    <input type="number" name="qty_in[]" min="1" class="form-control" required>
                                </div>

                                <div class="col-md-2 mb-2">
                                    <label class="form-label fw-bold">Remarks</label>
                                    <input type="text" name="remarks[]" class="form-control">
                                </div>

                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger w-100 remove-row">
                                        <i class="bi bi-dash-circle"></i>
                                    </button>
                                </div>

                            </div>

                        </div>

                        <button type="button" id="addRow" class="btn btn-secondary fw-bold mb-3">
                            <i class="bi bi-plus-circle"></i> Add Another Item
                        </button>

                        <button class="btn w-100 text-white fw-bold" style="background:#123765;">
                            Save Stock In
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {

            function initSelect2(element) {
                element.select2({
                    placeholder: "Search Item...",
                    width: '100%'
                });
            }

            initSelect2($('.select2-item'));

            $("#addRow").click(function() {
                let row = $(".stock-row").first().clone();

                row.find(".select2").remove();
                row.find(".select2-hidden-accessible").removeClass("select2-hidden-accessible");

                row.find("input").val("");
                row.find("select").val("");

                $("#stockRows").append(row);
                initSelect2(row.find(".select2-item"));
            });

            $(document).on("click", ".remove-row", function() {
                if ($(".stock-row").length > 1) {
                    $(this).closest(".stock-row").remove();
                }
            });

            $(".toggler-btn").click(function() {
                document.querySelector("#sidebar").classList.toggle("collapsed");
            });

        });
    </script>

</body>

</html>
