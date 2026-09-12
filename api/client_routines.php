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

$sql = "SELECT dia_semana, descripcion_rutina FROM RUTINAS_CLIENTE WHERE id_cliente = :id ORDER BY dia_semana ASC";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id_cliente]);
$rutinas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$formatted_routines = [];
for($i=1; $i<=7; $i++) {
    $formatted_routines[$i] = "";
}

foreach($rutinas as $r) {
    $formatted_routines[$r['dia_semana']] = $r['descripcion_rutina'];
}

echo json_encode([
    'success' => true,
    'rutinas' => $formatted_routines
]);
?>
