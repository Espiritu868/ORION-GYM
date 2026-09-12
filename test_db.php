<?php
require_once 'config/Database.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->query("SELECT TOP 1 id_cliente, DATALENGTH(fotografia) as len, SUBSTRING(fotografia, 1, 100) as prefix FROM CLIENTES");
$row = $stmt->fetch(PDO::FETCH_ASSOC);

print_r($row);
?>
