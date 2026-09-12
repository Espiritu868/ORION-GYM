<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/Database.php';

$id_cliente = $_GET['id_cliente'] ?? $_POST['id_cliente'] ?? 0;

if (empty($id_cliente)) {
    echo json_encode(['success' => false, 'message' => 'id_cliente requerido.']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Fetch all routines for this client
$stmt = $conn->prepare("SELECT * FROM RUTINA_DIAS WHERE id_cliente = :idc");
$stmt->execute([':idc' => $id_cliente]);
$rutinas_dias = $stmt->fetchAll(PDO::FETCH_ASSOC);

$formatted_routines = [];
for($i=1; $i<=7; $i++) {
    $formatted_routines[$i] = []; // Array of exercises
}

foreach($rutinas_dias as $rd) {
    $dia = $rd['dia_semana'];
    
    // Get exercises for this day
    $stmt_ej = $conn->prepare("
        SELECT re.id_rutina_ej, re.id_ejercicio, re.orden, re.descanso_segundos, c.nombre, c.imagen_url, c.grupo_muscular 
        FROM RUTINA_EJERCICIOS re 
        JOIN CATALOGO_EJERCICIOS c ON re.id_ejercicio = c.id_ejercicio 
        WHERE re.id_rutina_dia = :id 
        ORDER BY re.orden
    ");
    $stmt_ej->execute([':id' => $rd['id_rutina_dia']]);
    $ejercicios = $stmt_ej->fetchAll(PDO::FETCH_ASSOC);

    foreach ($ejercicios as &$ej) {
        // Get target sets
        $stmt_ser = $conn->prepare("SELECT numero_serie, lbs_obj, reps_obj, rpe_obj, tipo_serie FROM RUTINA_SERIES WHERE id_rutina_ej = :id ORDER BY numero_serie");
        $stmt_ser->execute([':id' => $ej['id_rutina_ej']]);
        $series = $stmt_ser->fetchAll(PDO::FETCH_ASSOC);
        
        // Get PREVIOUS performance for this exercise for this client
        // We look for the most recent ENTRENAMIENTO_LOG where this exercise was logged
        $stmt_prev = $conn->prepare("
            SELECT es.numero_serie, es.lbs_real, es.reps_real, es.rpe_real
            FROM ENTRENAMIENTO_SERIES_LOG es
            JOIN ENTRENAMIENTO_LOG el ON es.id_entrenamiento = el.id_entrenamiento
            WHERE el.id_cliente = :idc1 AND es.id_ejercicio = :iej1 AND el.finalizado = 1
            AND el.id_entrenamiento = (
                SELECT TOP 1 el2.id_entrenamiento 
                FROM ENTRENAMIENTO_LOG el2 
                JOIN ENTRENAMIENTO_SERIES_LOG es2 ON el2.id_entrenamiento = es2.id_entrenamiento
                WHERE el2.id_cliente = :idc2 AND es2.id_ejercicio = :iej2 AND el2.finalizado = 1
                ORDER BY el2.fecha DESC
            )
            ORDER BY es.numero_serie
        ");
        $stmt_prev->execute([':idc1' => $id_cliente, ':iej1' => $ej['id_ejercicio'], ':idc2' => $id_cliente, ':iej2' => $ej['id_ejercicio']]);
        $prev_series = $stmt_prev->fetchAll(PDO::FETCH_ASSOC);
        
        // Merge target and previous into one object per set
        $merged_series = [];
        foreach($series as $s) {
            $num = $s['numero_serie'];
            // Find if there is a previous log for this exact set number
            $prev_data = null;
            foreach($prev_series as $ps) {
                if($ps['numero_serie'] == $num) {
                    $prev_data = $ps;
                    break;
                }
            }
            
            $merged_series[] = [
                'numero_serie' => $num,
                'lbs_obj' => $s['lbs_obj'],
                'reps_obj' => $s['reps_obj'],
                'rpe_obj' => $s['rpe_obj'],
                'tipo_serie' => $s['tipo_serie'],
                'anterior' => $prev_data ? [
                    'lbs' => $prev_data['lbs_real'],
                    'reps' => $prev_data['reps_real'],
                    'rpe' => $prev_data['rpe_real']
                ] : null
            ];
        }
        
        $ej['series'] = $merged_series;
    }
    
    $formatted_routines[$dia] = $ejercicios;
}

echo json_encode([
    'success' => true,
    'rutinas' => $formatted_routines
]);
?>
