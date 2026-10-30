<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/Database.php';

$data = json_decode(file_get_contents('php://input'), true);

$id_cliente = $data['id_cliente'] ?? 0;
$dia_semana = $data['dia_semana'] ?? 0;
$duracion_segundos = $data['duracion_segundos'] ?? 0;
$ejercicios_log = $data['ejercicios_log'] ?? [];

if (empty($id_cliente) || empty($ejercicios_log)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

try {
    $conn->beginTransaction();

    // Create the workout log entry
    $stmt_log = $conn->prepare("INSERT INTO ENTRENAMIENTO_LOG (id_cliente, dia_semana, duracion_segundos, finalizado) VALUES (:idc, :dia, :dur, 1)");
    $stmt_log->execute([
        ':idc' => $id_cliente,
        ':dia' => $dia_semana,
        ':dur' => $duracion_segundos
    ]);
    
    $id_entrenamiento = $conn->lastInsertId();

    // Insert the sets
    foreach ($ejercicios_log as $ej) {
        $id_ejercicio = $ej['id_ejercicio'];
        foreach ($ej['series'] as $s) {
            // Only log if they checked it as completed
            if (isset($s['completada']) && $s['completada']) {
                $ins_ser = $conn->prepare("INSERT INTO ENTRENAMIENTO_SERIES_LOG (id_entrenamiento, id_ejercicio, numero_serie, lbs_real, reps_real, rpe_real, completada, tipo_serie) VALUES (:ide, :iej, :ns, :lbs, :reps, :rpe, 1, :tipo)");
                $ins_ser->execute([
                    ':ide' => $id_entrenamiento,
                    ':iej' => $id_ejercicio,
                    ':ns' => $s['numero_serie'],
                    ':lbs' => $s['lbs_real'] ?? 0,
                    ':reps' => $s['reps_real'] ?? 0,
                    ':rpe' => $s['rpe_real'] ?? 0,
                    ':tipo' => $s['tipo_serie'] ?? 'N'
                ]);
            }
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Entrenamiento guardado con éxito.']);
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al guardar el entrenamiento: ' . $e->getMessage()]);
}
?>
