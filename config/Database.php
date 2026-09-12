<?php
class Database {
    private $host = "170.80.140.2,6161";
    private $db_name = "ORION_GYM";
    private $username = "orionsys";
    private $password = "123";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("sqlsrv:Server=" . $this->host . ";Database=" . $this->db_name . ";TrustServerCertificate=true", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>
