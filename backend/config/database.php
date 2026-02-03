<?php
class Database {
    private $host = "localhost";
    private $db_name = "my_cinema";
    private $username = "root"; // mysql> CREATE USER 'cinema_user'@'localhost IDENTIFIED BY 'incroyable';
    private $password = "incroyable";
    public $conn;
    public function getConnection() {
        $this->conn = null;
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch(PDOException $exception) {
            echo json_encode(["error" => "Connection error: " . $exception->getMessage()]);
            exit;
        }
        return $this->conn;
    }
}
?>