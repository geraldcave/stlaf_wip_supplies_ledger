<?php
class StockOut
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }
    public function addStockOut($item_id, $qty_out, $remarks)
    {
        $stmt = $this->conn->prepare("SELECT qty_on_hand FROM items WHERE id=?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();

        if (!$res || $res['qty_on_hand'] < $qty_out) return -1;

        $stmt = $this->conn->prepare("UPDATE items SET qty_on_hand = qty_on_hand - ? WHERE id=?");
        $stmt->bind_param("ii", $qty_out, $item_id);
        $stmt->execute();

        $stmt = $this->conn->prepare("INSERT INTO stock_out (item_id, qty_out, date_out, remarks) VALUES (?, ?, NOW(), ?)");
        $stmt->bind_param("iis", $item_id, $qty_out, $remarks);
        return $stmt->execute();
    }

    public function getItems()
    {
        return $this->conn->query("SELECT id, description, qty_on_hand, unit FROM items ORDER BY description ASC");
    }

    public function getLedger()
    {
        return $this->conn->query("SELECT 
    i.description, 
    s.qty_out, 
    s.date_out
    FROM items i
    INNER JOIN stock_out s ON i.id = s.item_id
    ORDER BY i.description ASC, s.date_out DESC

    ");
    }
    // ledger get all 
    // SELECT 
    //         i.description, 
    //         IFNULL(s.qty_out, 0) AS qty_out, 
    //         s.date_out
    //     FROM items i
    //     LEFT JOIN stock_out s ON i.id = s.item_id
    //     ORDER BY i.description ASC, s.date_out DESC
    public function getStockOutStatistics()
    {
        // for even 0 can get
        // $sql = "SELECT i.description, COALESCE(SUM(s.qty_out), 0) AS total_qty_out
        //     FROM items i
        //     LEFT JOIN stock_out s ON i.id = s.item_id
        //     GROUP BY i.id
        //     ORDER BY i.description ASC";
        // return $this->conn->query($sql);
        $sql = "SELECT i.description, SUM(s.qty_out) AS total_qty_out
        FROM items i
        LEFT JOIN stock_out s ON i.id = s.item_id
        GROUP BY i.id
        HAVING SUM(s.qty_out) > 0
        ORDER BY i.description ASC";

        return $this->conn->query($sql);
    }
}
