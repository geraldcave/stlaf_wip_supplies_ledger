<?php
class StockOut {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function addStockOut($item_id, $qty_out, $remarks) {
        // Prevent negative or zero qty
        if ($qty_out <= 0) return false;

        // Check current item qty
        $check = $this->conn->prepare("SELECT qty_on_hand FROM items WHERE id=?");
        $check->bind_param("i", $item_id);
        $check->execute();
        $result = $check->get_result()->fetch_assoc();

        if (!$result || $result['qty_on_hand'] < $qty_out) {
            return -1; // Not enough stock
        }

        // Insert transaction
        $stmt = $this->conn->prepare("INSERT INTO stock_out (item_id, qty_out, remarks) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $item_id, $qty_out, $remarks);
        $save = $stmt->execute();

        if (!$save) return false;

        // Update item quantity
        $stmt2 = $this->conn->prepare("UPDATE items SET qty_on_hand = qty_on_hand - ? WHERE id=?");
        $stmt2->bind_param("ii", $qty_out, $item_id);

        return $stmt2->execute();
    }

    public function getItems() {
        return $this->conn->query("SELECT id, description, qty_on_hand, unit FROM items ORDER BY description ASC");
    }
}
