<?php
require_once dirname(__DIR__) . '/config/Database.php';

class Plan {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function listAll($activeOnly = false) {
        $sql = "SELECT id_plan, nombre_plan, descripcion, precio, duracion_dias, estado FROM PLANES";
        if ($activeOnly) {
            $sql .= " WHERE estado = 1";
        }
        $sql .= " ORDER BY precio ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $sql = "SELECT * FROM PLANES WHERE id_plan = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $sql = "INSERT INTO PLANES (nombre_plan, descripcion, precio, duracion_dias, estado) 
                VALUES (?, ?, ?, ?, 1)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nombre_plan'],
            $data['descripcion'],
            $data['precio'],
            $data['duracion_dias']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE PLANES SET 
                nombre_plan = ?, 
                descripcion = ?, 
                precio = ?, 
                duracion_dias = ? 
                WHERE id_plan = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nombre_plan'],
            $data['descripcion'],
            $data['precio'],
            $data['duracion_dias'],
            $id
        ]);
    }

    public function changeState($id, $state) {
        $sql = "UPDATE PLANES SET estado = ? WHERE id_plan = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$state, $id]);
    }

    public function hasActiveMemberships($id_plan) {
        $sql = "SELECT COUNT(*) FROM MEMBRESIAS 
                WHERE id_plan = ? 
                AND estado_membresia IN ('Activa', 'Pendiente')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_plan]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
?>
