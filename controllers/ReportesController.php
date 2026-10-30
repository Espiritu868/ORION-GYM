<?php
session_start();
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/Database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if (!hasPerm('reportes_view')) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit;
}

$action = $_GET['action'] ?? '';
$db = (new Database())->getConnection();

if ($action === 'resumen') {
    $desde = $_GET['desde'] ?? date('Y-m-01');
    $hasta = $_GET['hasta'] ?? date('Y-m-d');
    
    // Add time to cover full day
    $hasta = $hasta . ' 23:59:59';

    try {
        // Ingresos
        $stmtI = $db->prepare("SELECT ISNULL(SUM(monto), 0) FROM CAJA_TRANSACCIONES WHERE tipo_transaccion = 'Ingreso' AND fecha_transaccion BETWEEN ? AND ?");
        $stmtI->execute([$desde, $hasta]);
        $ingresos = $stmtI->fetchColumn();

        // Egresos
        $stmtE = $db->prepare("SELECT ISNULL(SUM(monto), 0) FROM CAJA_TRANSACCIONES WHERE tipo_transaccion = 'Egreso' AND fecha_transaccion BETWEEN ? AND ?");
        $stmtE->execute([$desde, $hasta]);
        $egresos = $stmtE->fetchColumn();

        // Nuevas Membresías
        $stmtM = $db->prepare("SELECT COUNT(*) FROM MEMBRESIAS WHERE fecha_inicio BETWEEN ? AND ?");
        $stmtM->execute([$desde, $hasta]);
        $nuevas_membresias = $stmtM->fetchColumn();

        // Asistencias (Check-ins)
        $stmtA = $db->prepare("SELECT COUNT(*) FROM ASISTENCIA WHERE fecha_hora BETWEEN ? AND ?");
        $stmtA->execute([$desde, $hasta]);
        $asistencias = $stmtA->fetchColumn();

        // Totales Actuales (Sin importar fecha)
        $stmtActivos = $db->query("SELECT COUNT(*) FROM MEMBRESIAS WHERE estado_membresia = 'Activa'");
        $activos = $stmtActivos->fetchColumn();

        $stmtInactivos = $db->query("SELECT COUNT(*) FROM CLIENTES WHERE estado = 0");
        $inactivos = $stmtInactivos->fetchColumn();

        echo json_encode([
            'success' => true,
            'ingresos' => number_format($ingresos, 2),
            'egresos' => number_format($egresos, 2),
            'balance' => number_format($ingresos - $egresos, 2),
            'nuevas_membresias' => $nuevas_membresias,
            'asistencias' => $asistencias,
            'miembros_activos' => $activos,
            'miembros_inactivos' => $inactivos
        ]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
}
