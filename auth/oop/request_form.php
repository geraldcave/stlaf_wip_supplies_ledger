<?php
require_once __DIR__ . '/../../sql/config.php';
require_once "../../backend/guest/assets/emailing.php";

class Request
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function insertRequest($name, $department, $itemName, $size, $product_id, $quantity, $unit)
    {
        $sql = "INSERT INTO req_form 
        (name, department, item, size, product_id, quantity, unit, date_req, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, 'Pending')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssis", $name, $department, $itemName, $size, $product_id, $quantity, $unit);

        $executed = $stmt->execute();

        if ($executed) {
            sendSupplyRequestEmail($name, $department, $itemName, $product_id, $unit, $quantity);
        }

        return $executed;
    }


    public function getAllRequests()
    {
        $sql = "SELECT * FROM req_form ORDER BY date_req DESC";
        $result = $this->conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function updateStatus($req_id, $status, $cancel_reason = null)
    {
        $sql = "UPDATE req_form SET status = ?, cancel_reason = ? WHERE req_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ssi", $status, $cancel_reason, $req_id);
        return $stmt->execute();
    }
}

$db = new Database();
$conn = $db->getConnection();
$request = new Request($conn);

// 🟩 Handle AJAX Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['method'] ?? '') === 'updateStatus') {
    header('Content-Type: application/json');

    $req_id = $_POST['req_id'] ?? null;
    $status = $_POST['status'] ?? null;
    $reason = $_POST['reason'] ?? null;

    if (!$req_id || !$status) {
        echo json_encode(['status' => 'error', 'message' => 'Missing parameters']);
        exit;
    }

    $success = $request->updateStatus($req_id, $status, $reason);
    echo json_encode([
        'status' => $success ? 'success' : 'error',
        'message' => $success ? 'Status updated successfully' : 'Failed to update status'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST['method'])) {
    // form submission logic
    $name        = $_POST['name'] ?? '';
    $department  = $_POST['department'] ?? '';
    $items       = $_POST['item'] ?? [];
    $sizes       = $_POST['size'] ?? [];
    $product_ids = $_POST['product_id'] ?? [];
    $quantities  = $_POST['quantity'] ?? [];
    $units       = $_POST['unit'] ?? [];

    // ensure all arrays have the same length
    $count = count($items);

    if ($department === "all") {
        $departments = ['HR', 'ACCOUNTING', 'CORPORATE', 'OPS', 'LITIGATION', 'MARKETING', 'IT'];
        $ok = true;
        foreach ($departments as $dept) {
            for ($i = 0; $i < $count; $i++) {
                $itemName = $items[$i];
                $size = $sizes[$i] ?? '';
                $product_id = $product_ids[$i];
                $quantity = $quantities[$i];
                $unit = $units[$i];

                if (!$request->insertRequest($name, $dept, $itemName, $size, $product_id, $quantity, $unit)) {
                    $ok = false;
                }
            }
        }
        echo "<script>alert('" . ($ok ? "Requests submitted to all departments and emails sent!" : "Some requests failed.") . "');</script>";
    } else {
        $ok = true;
        for ($i = 0; $i < $count; $i++) {
            $itemName = $items[$i];
            $size = $sizes[$i] ?? '';
            $product_id = $product_ids[$i];
            $quantity = $quantities[$i];
            $unit = $units[$i];

            if (!$request->insertRequest($name, $department, $itemName, $size, $product_id, $quantity, $unit)) {
                $ok = false;
            }
        }

        echo "<script>alert('" . ($ok ? "Request submitted and email sent successfully!" : "Failed to submit one or more requests.") . "');</script>";
    }

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
