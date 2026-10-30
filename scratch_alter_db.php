<?php
require_once 'config/Database.php';
$db = new Database();
$conn = $db->getConnection();

if ($conn) {
    try {
        $sql = "
        ALTER TABLE RUTINA_SERIES ALTER COLUMN reps_obj VARCHAR(20);
        ALTER TABLE RUTINA_SERIES ADD tipo_serie CHAR(1) DEFAULT 'N';
        ALTER TABLE ENTRENAMIENTO_SERIES_LOG ADD tipo_serie CHAR(1) DEFAULT 'N';
        ";
        $conn->exec($sql);
        echo "Database schema updated successfully.\n";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "No connection.\n";
}
?>
