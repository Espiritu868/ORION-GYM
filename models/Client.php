<?php
require_once dirname(__DIR__) . '/config/Database.php';

class Client {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function listAll($activeOnly = true) {
        $sql = "SELECT id_cliente, identidad, nombre_completo, telefono, email, direccion, estado, fecha_ingreso, 
                CASE WHEN DATALENGTH(fotografia) > 0 THEN 1 ELSE 0 END AS tiene_foto 
                FROM CLIENTES";
        if ($activeOnly) {
            $sql .= " WHERE estado = 1";
        }
        $sql .= " ORDER BY nombre_completo ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPhoto($id) {
        $sql = "SELECT fotografia FROM CLIENTES WHERE id_cliente = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }

    public function create($data) {
        $sql = "INSERT INTO CLIENTES (identidad, nombre_completo, telefono, email, direccion, fotografia, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['identidad'],
            $data['nombre_completo'],
            $data['telefono'],
            $data['email'],
            $data['direccion'],
            $data['fotografia'] // Base64 string
        ]);
    }

    public function update($id, $data) {
        if (!empty($data['fotografia'])) {
            $sql = "UPDATE CLIENTES SET 
                    identidad = ?, 
                    nombre_completo = ?, 
                    telefono = ?, 
                    email = ?, 
                    direccion = ?, 
                    fotografia = ? 
                    WHERE id_cliente = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['identidad'],
                $data['nombre_completo'],
                $data['telefono'],
                $data['email'],
                $data['direccion'],
                $data['fotografia'],
                $id
            ]);
        } else {
            $sql = "UPDATE CLIENTES SET 
                    identidad = ?, 
                    nombre_completo = ?, 
                    telefono = ?, 
                    email = ?, 
                    direccion = ? 
                    WHERE id_cliente = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['identidad'],
                $data['nombre_completo'],
                $data['telefono'],
                $data['email'],
                $data['direccion'],
                $id
            ]);
        }
    }

    public function changeState($id, $state) {
        $sql = "UPDATE CLIENTES SET estado = ? WHERE id_cliente = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$state, $id]);
    }
}
?>
