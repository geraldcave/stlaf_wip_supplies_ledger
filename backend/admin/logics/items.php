<?php
class Item
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function addItem($description, $unit, $unit_price, $supplier, $department, $threshold)
    {

        if ($unit_price < 0) $unit_price = 0;
        if ($threshold < 0) $threshold = 0;

        $stmt = $this->conn->prepare("
            INSERT INTO items (description, unit, unit_price, supplier, department, threshold)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param("ssdssi", $description, $unit, $unit_price, $supplier, $department, $threshold);
        return $stmt->execute();
    }

    public function getAllItems()
    {
        $sql = "SELECT id, description, unit, qty_on_hand, threshold FROM items ORDER BY description ASC";
        return $this->conn->query($sql);
    }
}
