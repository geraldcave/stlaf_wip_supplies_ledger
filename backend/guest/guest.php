<?php
session_start();
require_once '../../sql/config.php';
require_once '../../auth/oop/request_form.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['department']) !== 'employee') {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$firstname  = ucfirst($_SESSION['username'] ?? 'Guest');
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
<style>
#itemList {
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
}
#loader {
    display: none;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    background: rgba(255,255,255,0.8);
    padding: 20px;
    border-radius: 10px;
}
</style>
</head>
<body>
<nav class="navbar px-5 bg-light">
    <div class="d-flex align-items-center gap-3">
        <a href="guest.php" class="navbar-brand m-0 p-0">
            <img src="../../assets/images/official_logo.png" alt="Logo" width="80" height="80">
        </a>
        <div class="d-flex gap-2">
            <a href="guest.php" class="btn btn-outline-primary fw-bold">Request Form</a>
            <a href="req_list.php" class="btn btn-outline-primary fw-bold">View Requests</a>
        </div>
    </div>
    <div>
        <a href="../../logout.php"><button class="logout-btn">Logout</button></a>
    </div>
</nav>

<div class="container mt-5">
    <div class="card shadow p-4 position-relative">
        <h4 class="text-center mb-4">Submit Supply Request</h4>
        <div id="loader">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p>Submitting request...</p>
        </div>
        <form method="POST" id="requestForm">
            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Your Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
                </div>
                <div class="col">
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
            </div>
            <div class="row mb-3 position-relative">
                <div class="col">
                    <label class="form-label">Search Item</label>
                    <input type="text" id="itemSearch" class="form-control" placeholder="Type to search..." autocomplete="off" required>
                    <div id="itemList" class="list-group position-absolute w-100"></div>
                    <input type="hidden" name="item" id="selectedItem">
                </div>
                <div class="col">
                    <label class="form-label">Product ID</label>
                    <input type="text" name="product_id" id="productID" class="form-control" readonly>
                </div>
                <div class="col">
                    <label class="form-label">Unit</label>
                    <input type="text" name="unit" id="unitField" class="form-control" readonly>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" required>
                </div>
            </div>
            <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-primary w-50">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script src="../../assets/bootstrap/bootstrap.bundle.min.js"></script>
<script>
const searchInput = document.getElementById("itemSearch");
const itemList = document.getElementById("itemList");
const selectedItemInput = document.getElementById("selectedItem");
const productIDInput = document.getElementById("productID");
const unitField = document.getElementById("unitField");
const form = document.getElementById("requestForm");
const loader = document.getElementById("loader");
const submitBtn = form.querySelector('button[type="submit"]');

const items = [
<?php
$first = true;
while ($row = $items->fetch_assoc()):
    if (!$first) echo ",";
    $first = false;
?>
{id: "<?= $row['id'] ?>", name: "<?= addslashes($row['description']) ?>", unit: "<?= $row['unit'] ?>"}
<?php endwhile; ?>
];

searchInput.addEventListener("input", function() {
    const query = this.value.toLowerCase().trim();
    itemList.innerHTML = "";
    if (!query) return;
    const results = items.filter(item => item.name.toLowerCase().includes(query));
    results.forEach(item => {
        const option = document.createElement("a");
        option.classList.add("list-group-item", "list-group-item-action");
        option.textContent = `${item.name} (${item.unit})`;
        option.onclick = () => {
            searchInput.value = item.name;
            selectedItemInput.value = item.name;
            productIDInput.value = item.id;
            unitField.value = item.unit;
            itemList.innerHTML = "";
        };
        itemList.appendChild(option);
    });
});

document.addEventListener("click", (e) => {
    if (!searchInput.contains(e.target) && !itemList.contains(e.target)) {
        itemList.innerHTML = "";
    }
});

form.addEventListener("submit", function(e) {
    e.preventDefault();
    loader.style.display = "block";
    submitBtn.disabled = true;

    const formData = new FormData(form);

    fetch("", { method: "POST", body: formData })
        .then(res => res.text())
        .then(response => {
            alert("Request submitted successfully!");
            form.reset();
            searchInput.value = "";
            selectedItemInput.value = "";
            productIDInput.value = "";
            unitField.value = "";
        })
        .catch(err => {
            console.error(err);
            alert("Failed to submit request.");
        })
        .finally(() => {
            loader.style.display = "none";
            submitBtn.disabled = false;
        });
});
</script>
</body>
</html>
