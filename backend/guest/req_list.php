<?php
session_start();
require_once '../../sql/config.php';
require_once '../../auth/oop/request_form.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$firstname  = ucfirst($_SESSION['username'] ?? 'Guest');
$department = ucfirst($_SESSION['department'] ?? 'Employee');
$db = new Database();
$conn = $db->getConnection();
$request = new Request($conn);

$sql = "SELECT * FROM req_form ORDER BY date_req DESC";
$result = $conn->query($sql);
$requests = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | View Requests</title>
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../../assets/images/sub_logo_light.png">
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>
    <nav class="navbar px-5 bg-light">
        <div class="d-flex align-items-center gap-3">
            <a href="guest.php" class="navbar-brand m-0 p-0">
                <img src="../../assets/images/official_logo.png" alt="Logo" width="100" height="80">
            </a>
            <div class="d-flex gap-2">
                <a href="guest.php" class="btn btn-outline-primary fw-bold">
                    Request Form
                </a>
                <a href="req_list.php" class="btn btn-outline-primary fw-bold">
                    View Requests
                </a>
            </div>
        </div>

        <div class="d-flex justify-content-end align-items-center gap-3">
            <a href="../../logout.php">
                <button class="logout-btn">Logout</button>
            </a>
        </div>
    </nav>
    <div class="container mt-5">
        <div class="card shadow p-4">
            <h4 class="text-center mb-4">All Requests</h4>
            <div class="d-flex justify-content-end mb-3">
                <input type="text" id="searchInput" class="form-control w-25" placeholder="🔍 Search request...">
            </div>
            <ul class="nav nav-tabs mb-4" id="requestTabs">
                <li class="nav-item">
                    <button class="nav-link active" data-status="all">All</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-status="Pending">Pending</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-status="Delivered">Approved</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-status="Cancelled">Declined</button>
                </li>
            </ul>
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-bordered table-hover text-center align-middle" id="requestsTable">
                    <thead class="table-primary">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Item</th>
                            <th>Size</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Date Requested</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $i => $row): ?>
                            <?php
                            $status = ucfirst($row['status']);
                            $badgeClass = match (strtolower($status)) {
                                'approved' => 'status-approved',
                                'declined' => 'status-rejected',
                                'rejected' => 'status-rejected',
                                default => 'status-pending'
                            };
                            ?>
                            <tr data-status="<?= htmlspecialchars($status) ?>">
                                <td><?= htmlspecialchars($row['req_id']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['item']) ?></td>
                                <td><?= htmlspecialchars($row['size'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['quantity']) ?></td>
                                <td><?= htmlspecialchars($row['unit']) ?></td>
                                <td><?= htmlspecialchars($row['department']) ?></td>
                                <?php
                                $status = ucfirst($row['status']);
                                $badgeClass = match (strtolower($status)) {
                                    'delivered' => 'status-delivered',
                                    'cancelled' => 'status-cancelled',
                                    default => 'status-pending'
                                };
                                ?>
                                <td>
                                    <span class="status-badge <?= $badgeClass ?>"><?= $status ?></span>
                                </td>
                                <td><?= date("M d, Y h:i A", strtotime($row['date_req'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="assets/re.js"></script>
</body>

</html>