<?php
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

// Fetch historical
$sql = "SELECT * FROM MEDIDAS_CLIENTE WHERE id_cliente = :id ORDER BY fecha_evaluacion ASC";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id_cliente]);
$medidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate differences for the latest one compared to the previous one
$latest = null;
$previous = null;
$differences = [];

if (count($medidas) >= 1) {
    $latest = $medidas[count($medidas) - 1];
}
if (count($medidas) >= 2) {
    $previous = $medidas[count($medidas) - 2];
    
    $keys = ['peso', 'pecho', 'brazo', 'cintura', 'cadera', 'pierna', 'pantorrilla'];
    foreach($keys as $k) {
        $differences[$k] = round($latest[$k] - $previous[$k], 2);
    }
}

echo json_encode([
    'success' => true,
    'historial' => $medidas,
    'ultimo_registro' => $latest,
    'diferencias' => $differences
]);
?>
