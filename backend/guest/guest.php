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
    <style>
        #itemList {
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000
        }

        #loader {
            display: none;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px
        }

        .scrollable-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 5px
        }

        .table-wrapper {
            max-height: 300px;
            overflow-y: auto;
            overflow-x: hidden
        }
    </style>
</head>

<body>
    <div class="container mt-2" style="height:80vh">
        <div class="card shadow p-4 position-relative" style="height:85vh">
            <h5 class="mb-4">Submit Supply Request</h5>
            <div id="loader">
                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                <p>Submitting request...</p>
            </div>
            <form method="POST" id="requestForm" style="height:100%;display:flex;flex-direction:column">
                <div class="mb-3">
                    <h5 class="mb-4">Contact Details</h5>
                    <label class="form-label">Your Name</label>
                    <input type="text" name="name" class="form-control" placeholder="Enter your name" required>
                </div>
                <div class="mb-4">
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
                <h5 class="mb-4">Request Details</h5>
                <div class="scrollable-content">
                    <div class="row mb-3 position-relative">
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
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Unit</label>
                            <input type="text" id="unitField" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Quantity</label>
                            <input type="number" id="quantityField" class="form-control">
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
                                    <th>Unit</th>
                                    <th>Quantity</th>
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
        const searchInput = document.getElementById("itemSearch"),
            itemList = document.getElementById("itemList"),
            selectedItemInput = document.getElementById("selectedItem"),
            productIDInput = document.getElementById("productID"),
            unitField = document.getElementById("unitField"),
            form = document.getElementById("requestForm"),
            loader = document.getElementById("loader"),
            submitBtn = form.querySelector('button[type="submit"]'),
            addItemBtn = document.getElementById("addItemBtn"),
            itemsTableBody = document.querySelector("#itemsTable tbody"),
            quantityField = document.getElementById("quantityField");
        const items = [<?php $first = true;
                        while ($row = $items->fetch_assoc()): if (!$first) echo ",";
                            $first = false; ?> {
                    id: "<?= $row['id'] ?>",
                    name: "<?= addslashes($row['description']) ?>",
                    unit: "<?= $row['unit'] ?>"
                }
            <?php endwhile; ?>
        ];
        searchInput.addEventListener("input", function() {
            const t = this.value.toLowerCase().trim();
            itemList.innerHTML = "";
            if (!t) return;
            items.filter(e => e.name.toLowerCase().includes(t)).forEach(e => {
                const tEl = document.createElement("a");
                tEl.classList.add("list-group-item", "list-group-item-action");
                tEl.textContent = `${e.name} (${e.unit})`;
                tEl.onclick = () => {
                    searchInput.value = e.name;
                    selectedItemInput.value = e.name;
                    productIDInput.value = e.id;
                    unitField.value = e.unit;
                    itemList.innerHTML = "";
                };
                itemList.appendChild(tEl);
            });
        });
        document.addEventListener("click", e => {
            if (!searchInput.contains(e.target) && !itemList.contains(e.target)) itemList.innerHTML = "";
        });
        addItemBtn.addEventListener("click", function() {
            const name = selectedItemInput.value,
                id = productIDInput.value,
                unit = unitField.value,
                qty = quantityField.value;
            if (!name || !id || !unit || !qty) {
                alert("Please fill all item fields before adding.");
                return;
            }
            const tr = document.createElement("tr");
            tr.innerHTML = `<td><input type="hidden" name="item[]" value="${name}">${name}</td>
                <td><input type="hidden" name="product_id[]" value="${id}">${id}</td>
                <td><input type="hidden" name="unit[]" value="${unit}">${unit}</td>
                <td><input type="hidden" name="quantity[]" value="${qty}">${qty}</td>
                <td><button type="button" class="btn btn-sm btn-danger remove-item">X</button></td>`;
            itemsTableBody.appendChild(tr);
            searchInput.value = "";
            selectedItemInput.value = "";
            productIDInput.value = "";
            unitField.value = "";
            quantityField.value = "";
        });
        itemsTableBody.addEventListener("click", function(t) {
            if (t.target.classList.contains("remove-item")) t.target.closest("tr").remove();
        });
        form.addEventListener("submit", function(e) {
            e.preventDefault();
            const itemRows = itemsTableBody.querySelectorAll("tr");
            if (itemRows.length === 0) {
                alert("Please add at least one item before submitting.");
                return;
            }
            loader.style.display = "block";
            submitBtn.disabled = true;
            const t = new FormData(form);
            fetch("", {
                method: "POST",
                body: t
            }).then(r => r.text()).then(() => {
                alert("Request submitted successfully!");
                form.reset();
                itemsTableBody.innerHTML = "";
            }).catch(() => {
                alert("Failed to submit request.");
            }).finally(() => {
                loader.style.display = "none";
                submitBtn.disabled = false;
            });
        });
        document.getElementById("cancelBtn").addEventListener("click", function() {
            form.reset();
            searchInput.value = "";
            selectedItemInput.value = "";
            productIDInput.value = "";
            unitField.value = "";
            quantityField.value = "";
            itemsTableBody.innerHTML = "";
        });
    </script>
</body>

</html>