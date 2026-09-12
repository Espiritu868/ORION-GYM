<?php
require_once dirname(__DIR__) . '/config/Database.php';

class Membership {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    // Auto-update expired memberships
    public function autoUpdateExpired() {
        $sql = "UPDATE MEMBRESIAS SET estado_membresia = 'Vencida' 
                WHERE estado_membresia = 'Activa' AND fecha_fin < CAST(GETDATE() AS DATE)";
        $this->db->query($sql);
    }

    public function listAll($filterStatus = 'todas') {
        $this->autoUpdateExpired(); // Ensure statuses are up to date before listing

        $sql = "SELECT m.id_membresia, m.fecha_inicio, m.fecha_fin, m.estado_membresia, m.fecha_cancelacion,
                       c.id_cliente, c.nombre_completo, c.identidad, c.telefono, CASE WHEN DATALENGTH(c.fotografia) > 0 THEN 1 ELSE 0 END as tiene_foto,
                       p.id_plan, p.nombre_plan, p.duracion_dias, p.precio 
                FROM MEMBRESIAS m
                JOIN CLIENTES c ON m.id_cliente = c.id_cliente
                JOIN PLANES p ON m.id_plan = p.id_plan ";
        
        if ($filterStatus === 'activas') {
            $sql .= " WHERE m.estado_membresia = 'Activa' ";
        } else if ($filterStatus === 'vencidas') {
            $sql .= " WHERE m.estado_membresia = 'Vencida' OR (m.estado_membresia = 'Activa' AND m.fecha_fin BETWEEN CAST(GETDATE() AS DATE) AND DATEADD(day, 3, CAST(GETDATE() AS DATE))) ";
        } else if ($filterStatus === 'canceladas') {
            $sql .= " WHERE m.estado_membresia = 'Cancelada' ";
        }
        
        $sql .= " ORDER BY m.fecha_fin ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function assign($idCliente, $idPlan, $fechaInicio) {
        // Fetch plan duration
        $sqlPlan = "SELECT duracion_dias FROM PLANES WHERE id_plan = ?";
        $stmtPlan = $this->db->prepare($sqlPlan);
        $stmtPlan->execute([$idPlan]);
        $plan = $stmtPlan->fetch(PDO::FETCH_ASSOC);
        
        if (!$plan) return false;
        
        $duracionDias = (int)$plan['duracion_dias'];
        
        $sql = "INSERT INTO MEMBRESIAS (id_cliente, id_plan, fecha_inicio, fecha_fin, estado_membresia)
                VALUES (?, ?, ?, DATEADD(day, $duracionDias, ?), 'Pendiente')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $idCliente, 
            $idPlan, 
            $fechaInicio,
            $fechaInicio
        ]);
    }

    public function updatePending($idMembresia, $idPlan, $fechaInicio) {
        // Fetch plan duration
        $sqlPlan = "SELECT duracion_dias FROM PLANES WHERE id_plan = ?";
        $stmtPlan = $this->db->prepare($sqlPlan);
        $stmtPlan->execute([$idPlan]);
        $plan = $stmtPlan->fetch(PDO::FETCH_ASSOC);
        
        if (!$plan) return false;
        
        $duracionDias = (int)$plan['duracion_dias'];
        
        // Update only if it's Pendiente
        $sql = "UPDATE MEMBRESIAS SET 
                id_plan = ?, 
                fecha_inicio = ?, 
                fecha_fin = DATEADD(day, $duracionDias, ?)
                WHERE id_membresia = ? AND estado_membresia = 'Pendiente'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $idPlan, 
            $fechaInicio,
            $fechaInicio,
            $idMembresia
        ]);
        
        return $stmt->rowCount() > 0;
    }

    public function renew($idMembresia) {
        // Find existing membership and plan
        $sqlM = "SELECT m.id_plan, m.fecha_fin, p.duracion_dias 
                 FROM MEMBRESIAS m 
                 JOIN PLANES p ON m.id_plan = p.id_plan 
                 WHERE m.id_membresia = ?";
        $stmtM = $this->db->prepare($sqlM);
        $stmtM->execute([$idMembresia]);
        $memb = $stmtM->fetch(PDO::FETCH_ASSOC);

        if (!$memb) return false;
        
        // If it's already expired, start from today. If it's active, extend from fecha_fin.
        $today = date('Y-m-d');
        if ($memb['fecha_fin'] < $today) {
            $baseDate = $today;
        } else {
            $baseDate = $memb['fecha_fin'];
        }

        $duracionDias = (int)$memb['duracion_dias'];

        $sql = "UPDATE MEMBRESIAS SET 
                fecha_fin = DATEADD(day, $duracionDias, ?), 
                estado_membresia = 'Activa',
                fecha_cancelacion = NULL
                WHERE id_membresia = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$baseDate, $idMembresia]);
    }

    public function cancel($idMembresia) {
        $sql = "UPDATE MEMBRESIAS SET 
                estado_membresia = 'Cancelada', 
                fecha_cancelacion = CAST(GETDATE() AS DATE) 
                WHERE id_membresia = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$idMembresia]);
    }
}
?>
