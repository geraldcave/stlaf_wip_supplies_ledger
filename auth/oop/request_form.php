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

    public function insertRequest($name, $department, $item, $size, $product_id, $quantity, $unit)
    {
        $sql = "INSERT INTO req_form 
            (name, department, item, size, product_id, quantity, unit, date_req, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, 'Pending')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssis", $name, $department, $item, $size, $product_id, $quantity, $unit);

        $executed = $stmt->execute();

        if ($executed) {
            sendSupplyRequestEmail($name, $department, $item, $product_id, $unit, $quantity);
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

// 🟦 Handle Normal Request Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['method'])) {

    $name        = $_POST['name'] ?? '';
    $department  = $_POST['department'] ?? '';
    $item        = $_POST['item'] ?? '';
    $size        = $_POST['size'] ?? '';
    $product_id  = $_POST['product_id'] ?? '';
    $quantity    = $_POST['quantity'] ?? 0;
    $unit        = $_POST['unit'] ?? '';

    if ($department === "all") {
        $departments = ['HR', 'ACCOUNTING', 'CORPORATE', 'OPS', 'LITIGATION', 'MARKETING', 'IT'];
        $ok = true;
        foreach ($departments as $dept) {
            if (!$request->insertRequest($name, $dept, $item, $size, $product_id, $quantity, $unit)) {
                $ok = false;
            }
        }
        echo "<script>alert('" . ($ok ? "Requests submitted to all departments and emails sent!" : "Some requests failed.") . "');</script>";
    } else {
        if ($request->insertRequest($name, $department, $item, $size, $product_id, $quantity, $unit)) {
            echo "<script>alert('Request submitted and email sent successfully!');</script>";
        } else {
            echo "<script>alert('Failed to submit request.');</script>";
        }
    }
}
