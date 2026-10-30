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
$identidad = $data['identidad'] ?? $_POST['identidad'] ?? '';

if (empty($identidad)) {
    echo json_encode(['success' => false, 'message' => 'Identidad requerida.']);
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT id_cliente, nombre_completo, fotografia, fecha_ingreso FROM CLIENTES WHERE identidad = :identidad AND estado = 1";
$stmt = $conn->prepare($sql);
$stmt->execute([':identidad' => $identidad]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cliente) {
    // Generate a simple token (in a real app use JWT)
    $token = bin2hex(random_bytes(16));
    echo json_encode([
        'success' => true,
        'token' => $token,
        'cliente' => $cliente
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cliente no encontrado o inactivo.']);
}
?>
