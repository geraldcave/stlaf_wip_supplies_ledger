<?php
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
$deptResult = $conn->query("SELECT DISTINCT department FROM req_form ORDER BY department ASC");
$departments = $deptResult ? $deptResult->fetch_all(MYSQLI_ASSOC) : [];
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
    <div class="container mt-2">
        <div class="card shadow p-4" style="height:85vh">
            <div class="d-flex justify-content-between">
                <h4 class="text-center mb-4">All Requests</h4>
                <input type="text" id="searchInput" class="form-control w-25" placeholder="🔍 Search request...">
            </div>
            <div class="row mb-3">
                <div class="col text-end">
                    <div class="dropdown d-inline-block">
                        <button class="btn btn-sm btn-outline-primary dropdown-toggle fw-semibold px-2 py-1"
                            type="button"
                            id="departmentDropdown"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            style="min-width: 140px;">
                            Filter: <span id="selectedDepartment">All</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="departmentDropdown">
                            <li><button class="dropdown-item active" data-department="all">All</button></li>
                            <?php foreach ($departments as $dept): ?>
                                <li>
                                    <button class="dropdown-item" data-department="<?= htmlspecialchars($dept['department']) ?>">
                                        <?= htmlspecialchars(ucfirst($dept['department'])) ?>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                <table class="table table-bordered table-hover text-center align-middle" id="requestsTable">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Item</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-center">Unit</th>
                            <th class="text-center">Department</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Date Requested</th>
                        </tr>
                    </thead>
                    <tbody id="requestsBody">
                        <?php foreach ($requests as $i => $row): ?>
                            <?php
                            $status = ucfirst($row['status']);
                            $badgeClass = match (strtolower($status)) {
                                'delivered' => 'status-delivered',
                                'cancelled' => 'status-cancelled',
                                default => 'status-pending'
                            };
                            ?>
                            <tr data-department="<?= htmlspecialchars($row['department']) ?>"> 
                                <td><?= htmlspecialchars($row['req_id']) ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['item']) ?></td>
                                <td><?= htmlspecialchars($row['quantity']) ?></td>
                                <td><?= htmlspecialchars($row['unit']) ?></td>
                                <td><?= htmlspecialchars($row['department']) ?></td>
                                <td>
                                    <span class="status-badge <?= $badgeClass ?>"><?= $status ?></span>
                                    <?php if (strtolower($status) === 'cancelled'): ?>
                                        <br>
                                        <small class="text-muted">
                                            Reason: <?= htmlspecialchars($row['cancel_reason'] ?? 'No reason provided') ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?= date("M d, Y h:i A", strtotime($row['date_req'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function loadRequests() {
        fetch('get_requests_table.php')
            .then(response => response.text())
            .then(html => {
                const tbody = document.getElementById('requestsBody');
                if (!tbody) return;

                const currentScroll = tbody.parentElement.scrollTop;
                const currentSearch = document.getElementById('searchInput')?.value.toLowerCase() || '';
                const selectedDept = document.querySelector('[data-department].active-filter')?.getAttribute('data-department') || 'all';

                tbody.innerHTML = html;

                if (currentSearch) {
                    const rows = tbody.querySelectorAll('tr');
                    rows.forEach(row => {
                        const text = row.innerText.toLowerCase();
                        row.style.display = text.includes(currentSearch) ? '' : 'none';
                    });
                }

                if (selectedDept && selectedDept !== 'all') {
                    const rows = tbody.querySelectorAll('tr');
                    rows.forEach(row => {
                        const dept = row.getAttribute('data-department');
                        row.style.display = (dept === selectedDept && row.style.display !== 'none') ? '' : 'none';
                    });
                }

                tbody.parentElement.scrollTop = currentScroll;
            })
            .catch(err => console.error(err));
    }

    setInterval(loadRequests, 3000);
    </script>

    <script src="../../assets/bootstrap/bootstrap.bundle.js"></script>
    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../../assets/bootstrap/all.min.js"></script>
    <script src="assets/re.js"></script>
</body>

</html>
