<?php
require 'app/config/Database.php';
$db = new Database();
$conn = $db->getConnection();
if ($conn) {
    echo "Conexión exitosa.\n";
    $stmt = $conn->query('SELECT nombre_usuario, password_hash, estado_usuario FROM USUARIOS');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo "Fallo al conectar.\n";
}
