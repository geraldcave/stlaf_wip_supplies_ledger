<?php
require_once __DIR__ . '/../../sql/config.php';

class Request {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function insertRequest($name, $department, $item, $size, $product_id, $quantity, $unit) {
        $sql = "INSERT INTO req_form 
                (name, department, item, size, product_id, quantity, unit, date_req)
                VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("sssssis", $name, $department, $item, $size, $product_id, $quantity, $unit);
        return $stmt->execute();
    }
}

$db = new Database();
$conn = $db->getConnection();
$request = new Request($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name        = $_POST['name'];
    $department  = $_POST['department'];
    $item        = $_POST['item'];
    $size        = $_POST['size'];
    $product_id  = $_POST['product_id'];
    $quantity    = $_POST['quantity'];
    $unit        = $_POST['unit'];

    if ($department === "all") {
        $departments = ['hr', 'accounting', 'corporate', 'litigation', 'marketing', 'it', 'ops'];
        $success = true;
        foreach ($departments as $dept) {
            if (!$request->insertRequest($name, $dept, $item, $size, $product_id, $quantity, $unit)) {
                $success = false;
            }
        }
        echo $success
            ? "<script>alert('Requests submitted to all departments successfully!');</script>"
            : "<script>alert('Some requests failed to submit.');</script>";
    } else {
        if ($request->insertRequest($name, $department, $item, $size, $product_id, $quantity, $unit)) {
            echo "<script>alert('Request submitted successfully!');</script>";
        } else {
            echo "<script>alert('Failed to submit request.');</script>";
        }
    }
}
?>
