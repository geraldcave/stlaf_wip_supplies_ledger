<?php
class StockIn
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    
    public function addItem($description, $unit, $unit_price, $supplier, $department, $threshold, $qty_on_hand)
    {
        $stmt = $this->conn->prepare("
            INSERT INTO items (description, unit, unit_price, supplier, department, threshold, qty_on_hand)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssdsiii", $description, $unit, $unit_price, $supplier, $department, $threshold, $qty_on_hand);
        return $stmt->execute();
    }

    public function addStockIn($item_id, $qty_in, $remarks, $supplier, $stock_date)
    {
        if ($qty_in <= 0) return false;

        $stmt = $this->conn->prepare("INSERT INTO stock_in (item_id, qty_in, remarks) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $item_id, $qty_in, $remarks);
        $insert = $stmt->execute();

        if (!$insert) return false;

        $stmt2 = $this->conn->prepare("
            UPDATE items 
            SET qty_on_hand = qty_on_hand + ?, 
                supplier = ?, 
                created_at = ? 
            WHERE id = ?
        ");

        $stmt2->bind_param("issi", $qty_in, $supplier, $stock_date, $item_id);

        return $stmt2->execute();
    }

    public function getItems()
    {
        $sql = "SELECT id, description, qty_on_hand, unit FROM items ORDER BY description ASC";
        return $this->conn->query($sql);
    }
}
