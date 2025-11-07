<?php
class StockIn {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // ✅ Add NEW Item to items table
    public function addItem($description, $unit, $unit_price, $supplier, $department, $threshold, $qty_on_hand) {
        $stmt = $this->conn->prepare("
            INSERT INTO items (description, unit, unit_price, supplier, department, threshold, qty_on_hand)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssdsiii", $description, $unit, $unit_price, $supplier, $department, $threshold, $qty_on_hand);
        return $stmt->execute();
    }

    // ✅ Stock-In function
    public function addStockIn($item_id, $qty_in, $remarks) {
        if ($qty_in <= 0) return false;

        // Insert into stock_in history
        $stmt = $this->conn->prepare("INSERT INTO stock_in (item_id, qty_in, remarks) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $item_id, $qty_in, $remarks);
        $insert = $stmt->execute();

        if (!$insert) return false;

        // Update qty in items table
        $stmt2 = $this->conn->prepare("UPDATE items SET qty_on_hand = qty_on_hand + ? WHERE id = ?");
        $stmt2->bind_param("ii", $qty_in, $item_id);

        return $stmt2->execute();
    }

    // ✅ Fetch for dropdown
    public function getItems() {
        $sql = "SELECT id, description, qty_on_hand, unit FROM items ORDER BY description ASC";
        return $this->conn->query($sql);
    }
}
