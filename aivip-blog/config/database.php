<?php
class Database {
    private $host = "HOSTNAME HERE";
    private $db_name = "DB NAME HERE";
    private $username = "DB USER HERE";
    private $password = "DB PSW HERE";
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
        } catch(Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }

        return $this->conn;
    }

    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
            $this->conn = null;
        }
    }
}