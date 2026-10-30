<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/Database.php';

$db = new Database();
$conn = $db->getConnection();

try {
    $stmt = $conn->query("SELECT * FROM CATALOGO_EJERCICIOS ORDER BY grupo_muscular, nombre");
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
