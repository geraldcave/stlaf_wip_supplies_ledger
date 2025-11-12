<?php
require_once '../../sql/config.php';
require_once '../../auth/oop/request_form.php';
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'employee') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}
$firstname = ucfirst($_SESSION['username'] ?? 'Guest');
$department = ucfirst($_SESSION['department'] ?? 'Employee');
$items = $conn->query("SELECT id, description, unit FROM items ORDER BY description ASC");
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
    <link rel="stylesheet" href="assets/loader.css">
</head>

<body>
    <div class="main-container">
        <div class="card shadow p-4 position-relative scroll-card" style="height:85vh">
            <h3 class="mb-2 fw-bold">Submit Supply Request</h3>
            <div id="loader">
                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                <p>Submitting request...</p>
            </div>
            <form method="POST" id="requestForm" style="max-height: 700px; overflow-y: auto; display:flex; flex-direction:column">
                <div class="mb-2">
                    <h5 class="mb-2 fw-bold">Contact Details</h5>
                    <label class="form-label">Your Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-select" required>
                        <option disabled selected>Select department</option>
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
                <h5 class="mb-2 fw-bold">Request Details</h5>
                <div class="scrollable-content">
                    <div class="row mb-2 position-relative">
                        <div class="col-md-8">
                            <label class="form-label">Item Name</label>
                            <input type="text" id="itemSearch" class="form-control" placeholder="Type to search..." autocomplete="off">
                            <div id="itemList" class="list-group position-absolute w-100"></div>
                            <input type="hidden" id="selectedItem">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Product ID</label>
                            <input type="text" id="productID" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="form-label">Quantity</label>
                            <input type="number" id="quantityField" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Unit</label>
                            <input type="text" id="unitField" class="form-control" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-secondary w-100" id="addItemBtn">+ Add Item</button>
                    </div>
                    <div class="table-wrapper">
                        <table class="table table-bordered" id="itemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Product ID</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th style="width:50px">Remove</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-2">
                    <button type="button" class="btn btn-outline-secondary" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary px-5">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
    <script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script>
        const items = <?php
                        $arr = [];
                        while ($row = $items->fetch_assoc()) {
                            $arr[] = [
                                'id' => $row['id'],
                                'name' => $row['description'],
                                'unit' => $row['unit']
                            ];
                        }
                        echo json_encode($arr);
                        ?>;
    </script>
    <script src="assets/guest.js"></script>

</body>

</html>