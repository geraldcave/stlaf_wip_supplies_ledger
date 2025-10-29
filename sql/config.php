<?php
if (!defined("BASE_URL")) {
    define("BASE_URL", "/stlaf_wip_supplies_ledger/");
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
class Database {
    private $host = "127.0.0.1";
    private $port = 3306;
    private $dbname = "stlaf_ledger_supplies";
    private $username = "root";
    private $password = "";
    private $conn;

    public function __construct() {
        $this->conn = new mysqli(
            $this->host,
            $this->username,
            $this->password,
            $this->dbname,
            $this->port
        );

        if ($this->conn->connect_error) {
            die("Connection failed (MySQLi): " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
