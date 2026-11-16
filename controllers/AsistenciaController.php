<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';
$db = (new Database())->getConnection();

if ($action === 'check_in') {
    if (!hasPerm('asistencia_create')) {
        echo json_encode(['success' => false, 'message' => 'Sin permisos']);
        exit;
    }

    $identidad = $_POST['identidad'] ?? '';
    
    if (empty($identidad)) {
        echo json_encode(['success' => false, 'message' => 'Por favor ingrese el DNI.']);
        exit;
    }

    try {
        // Find client ignoring dashes
        $stmtC = $db->prepare("SELECT id_cliente, nombre_completo, estado, CASE WHEN DATALENGTH(fotografia) > 0 THEN 1 ELSE 0 END as tiene_foto FROM CLIENTES WHERE REPLACE(identidad, '-', '') = REPLACE(?, '-', '')");
        $stmtC->execute([$identidad]);
        $cliente = $stmtC->fetch(PDO::FETCH_ASSOC);

        if (!$cliente) {
            echo json_encode(['success' => false, 'message' => 'Cliente no encontrado.', 'status_color' => 'danger']);
            exit;
        }

        if ($cliente['estado'] != 1) {
            echo json_encode([
                'success' => false, 
                'message' => 'Cliente inactivo en el sistema.', 
                'cliente' => $cliente['nombre_completo'],
                'tiene_foto' => $cliente['tiene_foto'],
                'id_cliente' => $cliente['id_cliente'],
                'status_color' => 'danger'
            ]);
            exit;
        }

        // Check active membership
        // A client might have multiple memberships (e.g. past ones), we look for an 'Activa' one.
        // We first auto-update expired ones to be sure.
        require_once dirname(__DIR__) . '/models/Membership.php';
        $membershipModel = new Membership();
        $membershipModel->autoUpdateExpired();

        // Verificar membresía activa o dentro del período de gracia (7 días posteriores a fecha_fin)
        $stmtM = $db->prepare("
            SELECT TOP 1 m.fecha_fin, p.nombre_plan,
                   DATEDIFF(day, CAST(GETDATE() AS DATE), m.fecha_fin) as dias_diferencia
            FROM MEMBRESIAS m
            JOIN PLANES p ON m.id_plan = p.id_plan
            WHERE m.id_cliente = ? 
              AND (
                  (m.estado_membresia = 'Activa' AND m.fecha_fin >= CAST(GETDATE() AS DATE))
                  OR 
                  (m.fecha_fin >= DATEADD(day, -7, CAST(GETDATE() AS DATE)))
              )
            ORDER BY m.fecha_fin DESC
        ");
        $stmtM->execute([$cliente['id_cliente']]);
        $activa = $stmtM->fetch(PDO::FETCH_ASSOC);

        if ($activa) {
            // Determinar si está en período de gracia
            $diasDif = (int)$activa['dias_diferencia'];
            $mensajeAcceso = 'ACCESO PERMITIDO';
            $statusColor = 'success';

            if ($diasDif < 0) {
                $diasGraciaRestantes = 7 + $diasDif;
                $mensajeAcceso = "ACCESO EN PERÍODO DE GRACIA (Venció hace " . abs($diasDif) . " días - Le quedan {$diasGraciaRestantes} días de gracia)";
                $statusColor = 'warning';
            }

            // Registrar asistencia
            $stmtInsert = $db->prepare("INSERT INTO ASISTENCIA (id_cliente, fecha_hora) VALUES (?, GETDATE())");
            $stmtInsert->execute([$cliente['id_cliente']]);

            echo json_encode([
                'success' => true,
                'message' => $mensajeAcceso,
                'cliente' => $cliente['nombre_completo'],
                'plan' => $activa['nombre_plan'],
                'vence' => $activa['fecha_fin'],
                'tiene_foto' => $cliente['tiene_foto'],
                'id_cliente' => $cliente['id_cliente'],
                'status_color' => $statusColor
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'ACCESO DENEGADO (Sin Membresía Activa)',
                'cliente' => $cliente['nombre_completo'],
                'tiene_foto' => $cliente['tiene_foto'],
                'id_cliente' => $cliente['id_cliente'],
                'status_color' => 'danger'
            ]);
        }

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error BD: ' . $e->getMessage()]);
    }
}

if ($action === 'list_recent') {
    try {
        $stmt = $db->query("
            SELECT TOP 15 
                a.id_asistencia,
                a.id_cliente,
                CONVERT(VARCHAR(19), a.fecha_hora, 120) as fecha_servidor,
                CONVERT(VARCHAR(19), a.fecha_biometrico, 120) as fecha_reloj,
                c.nombre_completo,
                c.identidad,
                CASE WHEN DATALENGTH(c.fotografia) > 0 THEN 1 ELSE 0 END as tiene_foto,
                (
                    SELECT TOP 1 p.nombre_plan 
                    FROM MEMBRESIAS m 
                    JOIN PLANES p ON m.id_plan = p.id_plan 
                    WHERE m.id_cliente = c.id_cliente AND m.estado_membresia = 'Activa'
                    ORDER BY m.fecha_fin DESC
                ) as plan_nombre
            FROM ASISTENCIA a
            JOIN CLIENTES c ON a.id_cliente = c.id_cliente
            ORDER BY a.fecha_hora DESC
        ");
        echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
