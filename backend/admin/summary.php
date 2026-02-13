<?php
session_start();
require_once '../../sql/config.php';
require_once 'logics/stock_out.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'admin') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$stock = new StockOut($conn);

// Capture all filters
$month = $_GET['month'] ?? null;
$year = $_GET['year'] ?? null;
$department = $_GET['department'] ?? null;

// Pass department to your statistics logic (Ensure your getStockOutStatistics method is updated to accept it)
$statsResult = $stock->getStockOutStatistics($month, $year, $department);

$stats = [];
while ($row = $statsResult->fetch_assoc()) {
    $stats[] = $row;
}

usort($stats, function ($a, $b) {
    return $b['total_qty_out'] - $a['total_qty_out'];
});

$firstname = ucfirst($_SESSION['username'] ?? 'Admin');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | Stock Out Statistics</title>
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../../assets/images/sub_logo_light.png">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/super.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .main {
            flex: 1;
            padding: 20px;
            min-height: 0;
        }

        #chart-container {
            height: 70vh;
            max-height: 70vh;
            overflow-y: auto;
        }

        #stockOutChart {
            display: block;
            width: 100% !important;
        }

        .filter-form-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
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

            <div class="filter-form-container">
                <form method="GET" id="filterForm" class="d-flex flex-wrap gap-2 mb-3">
                    <select name="month" class="form-select w-auto" id="monthSelect">
                        <option value="">All Months</option>
                        <?php
                        for ($m = 1; $m <= 12; $m++) {
                            $selected = ($month == $m) ? 'selected' : '';
                            echo "<option value='$m' $selected>" . date("F", mktime(0, 0, 0, $m, 1)) . "</option>";
                        }
                        ?>
                    </select>

                    <select name="year" class="form-select w-auto" id="yearSelect">
                        <option value="">All Years</option>
                        <?php
                        $startYear = 2024;
                        $currentYear = date("Y");
                        for ($y = $currentYear; $y >= $startYear; $y--) {
                            $selected = ($year == $y) ? 'selected' : '';
                            echo "<option value='$y' $selected>$y</option>";
                        }
                        ?>
                    </select>

                    <select name="department" class="form-select w-auto" id="deptSelect">
                        <option value="">All Departments</option>
                        <?php
                        $depts = ['HR', 'MARKETING', 'ACCOUNTING', 'LITIGATION', 'IT', 'ADMIN', 'CORPORATE', 'OPERATIONS'];
                        foreach ($depts as $d) {
                            $selected = ($department == $d) ? 'selected' : '';
                            echo "<option value='$d' $selected>$d</option>";
                        }
                        ?>
                    </select>

                    <a href="download_summary.php?month=<?= $month ?>&year=<?= $year ?>&department=<?= $department ?>" class="btn btn-danger">
                        <i class="bi bi-file-earmark-pdf"></i> Summary PDF
                    </a>
                    <a href="download_detailed_records.php?month=<?= $month ?>&year=<?= $year ?>&department=<?= $department ?>" class="btn btn-success">
                        <i class="bi bi-list-check"></i> Detailed PDF
                    </a>
                </form>

                <h3 class="fw-bold mb-4 text-center">Stock Out Statistics</h3>
                <div id="chart-container">
                    <canvas id="stockOutChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    <script>
        const stats = <?= json_encode($stats) ?>;

        // Auto-submit form on change for all dropdowns
        ['monthSelect', 'yearSelect', 'deptSelect'].forEach(id => {
            document.getElementById(id).addEventListener('change', () => {
                document.getElementById('filterForm').submit();
            });
        });
    </script>
    <script src="assets/sum.js"></script>
</body>

</html>