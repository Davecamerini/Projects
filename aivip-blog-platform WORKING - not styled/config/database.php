<?php
class Database {
    private $host = "db27.webme.it";
    private $db_name = "sitidi_684";
    private $username = "sitidi_684";
    private $password = "b4G6ywDZ";
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