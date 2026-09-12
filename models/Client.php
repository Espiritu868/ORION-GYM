<?php
require_once dirname(__DIR__) . '/config/Database.php';

class Client {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function listAll($stateFilter = '1') {
        $sql = "SELECT id_cliente, identidad, nombre_completo, telefono, email, direccion, estado, fecha_ingreso, 
                CASE WHEN DATALENGTH(fotografia) > 0 THEN 1 ELSE 0 END AS tiene_foto 
                FROM CLIENTES";
        if ($stateFilter === '1' || $stateFilter === '0') {
            $sql .= " WHERE estado = " . intval($stateFilter);
        }
        $sql .= " ORDER BY estado DESC, nombre_completo ASC";
        
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

    public function checkIdentity($identidad, $excludeId = null) {
        $sql = "SELECT nombre_completo, estado FROM CLIENTES WHERE identidad = ?";
        $params = [$identidad];
        if ($excludeId) {
            $sql .= " AND id_cliente != ?";
            $params[] = $excludeId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function hasActiveMembership($id_cliente) {
        $sql = "SELECT COUNT(*) FROM MEMBRESIAS 
                WHERE id_cliente = ? 
                AND estado_membresia IN ('Activa', 'Pendiente')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_cliente]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
?>
