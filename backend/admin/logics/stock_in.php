<?php
class StockIn {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Add stock-in transaction + update item quantity
    public function addStockIn($item_id, $qty_in, $remarks) {
        // Prevent zero or negative qty
        if ($qty_in <= 0) return false;

        // Insert into stock_in history
        $stmt = $this->conn->prepare("INSERT INTO stock_in (item_id, qty_in, remarks) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $item_id, $qty_in, $remarks);
        $result = $stmt->execute();

        if (!$result) return false;

        // Update items table qty_on_hand
        $stmt2 = $this->conn->prepare("UPDATE items SET qty_on_hand = qty_on_hand + ? WHERE id = ?");
        $stmt2->bind_param("ii", $qty_in, $item_id);

        return $stmt2->execute();
    }

    // Fetch items for dropdown
    public function getItems() {
        $sql = "SELECT id, description, qty_on_hand, unit FROM items ORDER BY description ASC";
        return $this->conn->query($sql);
    }
}
