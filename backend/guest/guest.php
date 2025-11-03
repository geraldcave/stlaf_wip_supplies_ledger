<?php
session_start();
require_once '../../sql/config.php';
require_once '../../auth/oop/request_form.php';


if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'employee') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$showGuestAlert = isset($_SESSION['show_guest_alert']) && $_SESSION['show_guest_alert'] === true;
unset($_SESSION['show_guest_alert']);


$firstname  = ucfirst($_SESSION['username'] ?? 'Guest');
$lastname   = '';
$department = ucfirst($_SESSION['department'] ?? 'Employee');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STLAF | Request Form</title>
    <link rel="stylesheet" href="../../assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="icon" type="image/png" href="../../assets/images/sub_logo_light.png">
    <style>
        :root {
            --midnight-cyan: #1A2634;
            --gold: #CCAA49;
            --prussian-blue: #123765;
        }
    </style>
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
            <?php
            $displayName = ($firstname === '' || strtolower($firstname) === 'guest_employee') ? 'Guest' : $firstname;
            ?>
            <!-- <span>Welcome, <?= htmlspecialchars($displayName) ?> (<?= htmlspecialchars($department) ?>)</span> -->
            <a href="../../logout.php">
                <button class="logout-btn">Logout</button>
            </a>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card shadow p-4">
            <h4 class="text-center mb-4">Submit Supply Request</h4>
            <form method="POST">
                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">Your Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
                    </div>
                    <div class="col">
                        <label class="form-label">Department</label>
                        <select name="department" class="form-select" required>
                            <option value="" disabled selected>Select department</option>
                            <option value="all">All Departments</option>
                            <option value="hr">HR</option>
                            <option value="accounting">Accounting</option>
                            <option value="corporate">Corporate</option>
                            <option value="litigation">Litigation</option>
                            <option value="marketing">Marketing</option>
                            <option value="it">IT</option>
                            <option value="ops">Operations</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">Item</label>
                        <input type="text" name="item" class="form-control" required>
                    </div>
                    <div class="col">
                        <label class="form-label">Size</label>
                        <input type="text" name="size" class="form-control">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label class="form-label">Product ID</label>
                        <input type="text" name="product_id" class="form-control">
                    </div>
                    <div class="col">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" required>
                    </div>
                    <div class="col">
                        <label class="form-label">Unit</label>
                        <select name="unit" class="form-select" required>
                            <option value="" disabled selected>Select unit</option>
                            <option value="Ream">Ream</option>
                            <option value="Box">Box</option>
                            <option value="Pack">Pack</option>
                            <option value="Pc/Pcs">Pc/s.</option>
                            <option value="Roll">Roll</option>
                            <option value="Bottle">Bottle</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn btn-primary w-50">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="guestAlertModal" tabindex="-1" aria-labelledby="guestAlertLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content text-center" style="border: 2px solid var(--gold); border-radius: 10px;">
                <div class="modal-header" style="background-color: var(--prussian-blue); color: var(--gold);">
                    <h5 class="modal-title w-100" id="guestAlertLabel">Guest Employee Dashboard</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="background-color: var(--midnight-cyan); color: white;">
                    <p>Welcome! You are logged in as a <strong style="color: var(--gold);">guest employee</strong>.</p>
                    <p>You have limited access to the system functionalities.</p>
                </div>
                <div class="modal-footer justify-content-center" style="background-color: var(--midnight-cyan); border-top: 1px solid var(--gold);">
                    <button type="button" class="btn" style="background-color: var(--gold); color: var(--midnight-cyan); font-weight: 600;" data-bs-dismiss="modal">
                        Okay
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
        <?php if ($showGuestAlert): ?>
            document.addEventListener("DOMContentLoaded", function() {
                var guestAlertModal = new bootstrap.Modal(document.getElementById("guestAlertModal"));
                guestAlertModal.show();
            });
        <?php endif; ?>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="../../assets/bootstrap/all.min.js"></script>
</body>

</html>