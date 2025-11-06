<?php
class Item {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function addItem($description, $unit, $unit_price, $supplier, $department, $threshold) {

        if ($unit_price < 0) $unit_price = 0;
        if ($threshold < 0) $threshold = 0;

        $stmt = $this->conn->prepare("
            INSERT INTO items (description, unit, unit_price, supplier, department, threshold)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("ssdssi", $description, $unit, $unit_price, $supplier, $department, $threshold);
        return $stmt->execute();
    }

    public function getAllItems() {
        $result = $this->conn->query("SELECT * FROM items ORDER BY description ASC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
